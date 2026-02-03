<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarginSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'minimum_margin_percentage',
        'warning_margin_percentage',
        'critical_margin_percentage',
        'alert_enabled',
        'block_negative_margin',
        'require_approval_for_low_margin',
    ];

    protected $casts = [
        'minimum_margin_percentage' => 'decimal:2',
        'warning_margin_percentage' => 'decimal:2',
        'critical_margin_percentage' => 'decimal:2',
        'alert_enabled' => 'boolean',
        'block_negative_margin' => 'boolean',
        'require_approval_for_low_margin' => 'boolean',
    ];

    /**
     * Relazione: appartiene a un'agenzia
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get margin level for a given percentage
     */
    public function getMarginLevel(float $marginPercentage): string
    {
        if (!$this->alert_enabled) {
            return 'none';
        }

        if ($marginPercentage < 0) {
            return 'critical';
        } elseif ($marginPercentage < $this->critical_margin_percentage) {
            return 'critical';
        } elseif ($marginPercentage < $this->warning_margin_percentage) {
            return 'warning';
        } elseif ($marginPercentage < $this->minimum_margin_percentage) {
            return 'info';
        } else {
            return 'good';
        }
    }

    /**
     * Get margin color for a given percentage
     */
    public function getMarginColor(float $marginPercentage): string
    {
        $level = $this->getMarginLevel($marginPercentage);

        return match ($level) {
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
    public function marginRequiresApproval(float $marginPercentage): bool
    {
        if (!$this->require_approval_for_low_margin) {
            return false;
        }

        $level = $this->getMarginLevel($marginPercentage);
        return in_array($level, ['critical', 'warning']);
    }

    /**
     * Check if margin is acceptable
     */
    public function marginIsAcceptable(float $marginPercentage): bool
    {
        if (!$this->block_negative_margin) {
            return true;
        }

        return $marginPercentage >= 0;
    }
}
