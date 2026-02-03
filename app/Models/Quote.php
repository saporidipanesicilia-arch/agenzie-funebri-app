<?php


namespace App\Models;

use App\Infrastructure\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Quote extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'funeral_id',
        'agency_id',
        'branch_id',
        'quote_number',
        'status',
        'valid_until',
        'discount_percentage',
        'discount_amount',
        'created_by_user_id',
        'approved_by_user_id',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (empty($quote->uuid)) {
                $quote->uuid = (string) Str::uuid();
            }

            // Auto-generate quote number
            if (empty($quote->quote_number)) {
                $quote->quote_number = static::generateQuoteNumber($quote->agency_id);
            }
        });
    }

    /**
     * Generate unique quote number for agency
     */
    protected static function generateQuoteNumber(int $agencyId): string
    {
        $year = now()->year;
        $lastQuote = static::where('agency_id', $agencyId)
            ->where('quote_number', 'like', $year . '/%')
            ->orderByDesc('id')
            ->first();

        if (!$lastQuote) {
            $number = 1;
        } else {
            preg_match('/\/(\d+)$/', $lastQuote->quote_number, $matches);
            $number = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        }

        return $year . '/' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Relazione: appartiene a un funerale
     */
    public function funeral()
    {
        return $this->belongsTo(Funeral::class);
    }

    /**
     * Relazione: appartiene a una sede
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relazione: creato da utente
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Relazione: approvato da utente
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Relazione: righe preventivo
     */
    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    /**
     * Calculate total cost (before discount)
     */
    public function getTotalCostAttribute(): float
    {
        return (float) $this->items->sum('total_cost');
    }

    /**
     * Calculate total selling price (before discount)
     */
    public function getTotalSellingAttribute(): float
    {
        return (float) $this->items->sum('total_selling');
    }

    /**
     * Calculate discount amount applied
     */
    public function getDiscountAppliedAttribute(): float
    {
        $discountFromPercentage = ($this->total_selling * $this->discount_percentage) / 100;
        $discountFromAmount = (float) $this->discount_amount;

        return $discountFromPercentage + $discountFromAmount;
    }

    /**
     * Calculate final total (after discount)
     */
    public function getFinalTotalAttribute(): float
    {
        return max(0, $this->total_selling - $this->discount_applied);
    }

    /**
     * Calculate margin amount (€) after discount
     */
    public function getMarginAmountAttribute(): float
    {
        return $this->final_total - $this->total_cost;
    }

    /**
     * Calculate margin percentage (%)
     */
    public function getMarginPercentageAttribute(): float
    {
        if ($this->final_total == 0) {
            return 0;
        }

        return ($this->margin_amount / $this->final_total) * 100;
    }

    /**
     * Get margin alert level based on agency settings
     */
    public function getMarginAlertLevelAttribute(): string
    {
        $settings = MarginSettings::where('agency_id', $this->agency_id)->first();

        if (!$settings || !$settings->alert_enabled) {
            return 'none';
        }

        $percentage = $this->margin_percentage;

        if ($percentage < 0) {
            return 'critical'; // Losing money (RED)
        } elseif ($percentage < $settings->critical_margin_percentage) {
            return 'critical'; // Below critical threshold (ORANGE)
        } elseif ($percentage < $settings->warning_margin_percentage) {
            return 'warning'; // Below warning threshold (YELLOW)
        } elseif ($percentage < $settings->minimum_margin_percentage) {
            return 'info'; // Below minimum but acceptable (BLUE)
        } else {
            return 'good'; // Above minimum (GREEN)
        }
    }

    /**
     * Get margin color
     */
    public function getMarginColorAttribute(): string
    {
        return match ($this->margin_alert_level) {
            'critical' => 'red',
            'warning' => 'orange',
            'info' => 'yellow',
            'good' => 'green',
            default => 'gray',
        };
    }

    /**
     * Check if margin requires approval
     */
    public function requiresApproval(): bool
    {
        $settings = MarginSettings::where('agency_id', $this->agency_id)->first();

        if (!$settings || !$settings->require_approval_for_low_margin) {
            return false;
        }

        return in_array($this->margin_alert_level, ['critical', 'warning']);
    }

    /**
     * Check if quote can be accepted (not blocked by negative margin)
     */
    public function canBeAccepted(): bool
    {
        $settings = MarginSettings::where('agency_id', $this->agency_id)->first();

        if (!$settings || !$settings->block_negative_margin) {
            return true;
        }

        return $this->margin_amount >= 0;
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): bool
    {
        return $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Accept quote
     */
    public function accept(): bool
    {
        if (!$this->canBeAccepted()) {
            throw new \Exception('Cannot accept quote with negative margin');
        }

        return $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Reject quote
     */
    public function reject(string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Check if quote is expired
     */
    public function isExpired(): bool
    {
        if (!$this->valid_until) {
            return false;
        }

        return $this->valid_until->isPast();
    }

    /**
     * Scope: draft quotes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: sent quotes
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope: accepted quotes
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope: quotes needing approval
     */
    public function scopeNeedingApproval($query)
    {
        return $query->whereNull('approved_by_user_id')
            ->where('status', 'draft');
    }
}
