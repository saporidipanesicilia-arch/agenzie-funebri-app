<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Concession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'grave_id',
        'burial_id',
        'concessionaire_name',
        'concessionaire_tax_code',
        'concessionaire_phone',
        'concessionaire_email',
        'concessionaire_address',
        'concession_date',
        'expiry_date',
        'duration_years',
        'renewal_count',
        'last_renewal_date',
        'auto_renewal',
        'status',
        'fee_paid',
        'fee_paid_date',
        'notes',
    ];

    protected $casts = [
        'concession_date' => 'date',
        'expiry_date' => 'date',
        'last_renewal_date' => 'date',
        'fee_paid_date' => 'date',
        'auto_renewal' => 'boolean',
        'fee_paid' => 'decimal:2',
        'duration_years' => 'integer',
        'renewal_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($concession) {
            if (empty($concession->uuid)) {
                $concession->uuid = (string) Str::uuid();
            }

            // Auto-calculate expiry date if not set
            if (empty($concession->expiry_date) && $concession->duration_years) {
                $concession->expiry_date = $concession->concession_date
                    ->copy()
                    ->addYears($concession->duration_years);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Relazione: tomba
     */
    public function grave()
    {
        return $this->belongsTo(Grave::class);
    }

    /**
     * Relazione: sepoltura
     */
    public function burial()
    {
        return $this->belongsTo(Burial::class);
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if concession is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    /**
     * Check if concession is expiring soon (< 90 days)
     */
    public function isExpiringSoon(): bool
    {
        return !$this->isExpired() && $this->days_until_expiry <= 90;
    }

    /**
     * Check if concession is in critical period (< 30 days)
     */
    public function isExpiringCritical(): bool
    {
        return !$this->isExpired() && $this->days_until_expiry <= 30;
    }

    /**
     * Get expiration alert level
     */
    public function getExpirationAlertLevelAttribute(): string
    {
        if ($this->isExpired()) {
            return 'expired'; // 🔴
        } elseif ($this->days_until_expiry <= 30) {
            return 'critical'; // 🟠
        } elseif ($this->days_until_expiry <= 90) {
            return 'warning'; // 🟡
        } else {
            return 'active'; // 🟢
        }
    }

    /**
     * Get expiration color
     */
    public function getExpirationColorAttribute(): string
    {
        return match ($this->expiration_alert_level) {
            'expired' => 'red',
            'critical' => 'orange',
            'warning' => 'yellow',
            default => 'green',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Attiva',
            'expiring' => 'In scadenza',
            'expired' => 'Scaduta',
            'renewed' => 'Rinnovata',
            'terminated' => 'Cessata',
            default => 'Sconosciuta',
        };
    }

    /**
     * Renew concession
     */
    public function renew(int $additionalYears, ?float $feePaid = null): bool
    {
        $newExpiryDate = $this->expiry_date->copy()->addYears($additionalYears);

        return $this->update([
            'expiry_date' => $newExpiryDate,
            'duration_years' => $this->duration_years + $additionalYears,
            'renewal_count' => $this->renewal_count + 1,
            'last_renewal_date' => now(),
            'status' => 'active',
            'fee_paid' => $feePaid ?? $this->fee_paid,
            'fee_paid_date' => $feePaid ? now() : $this->fee_paid_date,
        ]);
    }

    /**
     * Terminate concession
     */
    public function terminate(string $reason = null): bool
    {
        $this->update([
            'status' => 'terminated',
            'notes' => $reason ? $this->notes . "\n\nCessata: " . $reason : $this->notes,
        ]);

        // Mark grave as available
        $this->grave->update(['status' => 'available']);

        return true;
    }

    /**
     * Auto-update status based on expiry date
     * Should be called via cron job
     */
    public static function updateExpirationStatuses(): void
    {
        // Mark as expiring (< 90 days)
        static::where('status', 'active')
            ->whereBetween('expiry_date', [now(), now()->addDays(90)])
            ->update(['status' => 'expiring']);

        // Mark as expired (past expiry date)
        static::whereIn('status', ['active', 'expiring'])
            ->where('expiry_date', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Scope: active concessions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: expiring concessions
     */
    public function scopeExpiring($query)
    {
        return $query->where('status', 'expiring');
    }

    /**
     * Scope: expired concessions
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope: expiring within days
     */
    public function scopeExpiringWithinDays($query, int $days)
    {
        return $query->whereIn('status', ['active', 'expiring'])
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    /**
     * Scope: expiring soon (< 90 days)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->expiringWithinDays(90);
    }

    /**
     * Scope: expiring critical (< 30 days)
     */
    public function scopeExpiringCritical($query)
    {
        return $query->expiringWithinDays(30);
    }
}
