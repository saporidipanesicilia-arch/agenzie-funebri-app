<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'quote_id',
        'item_type',
        'description',
        'cost_price',
        'selling_price',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->uuid)) {
                $item->uuid = (string) Str::uuid();
            }
        });

        // Recalculate quote totals when item changes
        static::saved(function ($item) {
            if ($item->quote) {
                $item->quote->touch(); // Triggers recalculation
            }
        });

        static::deleted(function ($item) {
            if ($item->quote) {
                $item->quote->touch();
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
     * Relazione: appartiene a un preventivo
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Calculate total cost
     */
    public function getTotalCostAttribute(): float
    {
        return (float) ($this->cost_price * $this->quantity);
    }

    /**
     * Calculate total selling price
     */
    public function getTotalSellingAttribute(): float
    {
        return (float) ($this->selling_price * $this->quantity);
    }

    /**
     * Calculate margin amount (€)
     */
    public function getMarginAmountAttribute(): float
    {
        return $this->total_selling - $this->total_cost;
    }

    /**
     * Calculate margin percentage (%)
     */
    public function getMarginPercentageAttribute(): float
    {
        if ($this->total_selling == 0) {
            return 0;
        }

        return ($this->margin_amount / $this->total_selling) * 100;
    }

    /**
     * Get margin color for UI
     */
    public function getMarginColorAttribute(): string
    {
        $percentage = $this->margin_percentage;

        if ($percentage < 0) {
            return 'red'; // Losing money
        } elseif ($percentage < 5) {
            return 'orange'; // Critical
        } elseif ($percentage < 15) {
            return 'yellow'; // Warning
        } elseif ($percentage < 25) {
            return 'blue'; // Below target
        } else {
            return 'green'; // Good
        }
    }

    /**
     * Get item type label in Italian
     */
    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'coffin' => 'Cofano',
            'flowers' => 'Fiori',
            'transport' => 'Trasporto',
            'service' => 'Servizio',
            'ceremony' => 'Cerimonia',
            'grave' => 'Loculo/Tomba',
            'documents' => 'Pratiche',
            default => 'Altro',
        };
    }

    /**
     * Scope: by item type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('item_type', $type);
    }
}
