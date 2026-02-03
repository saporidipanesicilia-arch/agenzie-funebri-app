<?php

namespace App\Models;

use App\Infrastructure\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Cemetery extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'agency_id',
        'branch_id',
        'name',
        'address',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'total_graves',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cemetery) {
            if (empty($cemetery->uuid)) {
                $cemetery->uuid = (string) Str::uuid();
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
     * Relazione: sede gestrice
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relazione: mappe del cimitero
     */
    public function maps()
    {
        return $this->hasMany(CemeteryMap::class)->orderBy('order');
    }

    /**
     * Relazione: aree/sezioni
     */
    public function areas()
    {
        return $this->hasMany(CemeteryArea::class);
    }

    /**
     * Get all graves through areas
     */
    public function graves()
    {
        return $this->hasManyThrough(Grave::class, CemeteryArea::class);
    }

    /**
     * Get available graves count
     */
    public function getAvailableGravesCountAttribute(): int
    {
        return $this->graves()->where('status', 'available')->count();
    }

    /**
     * Get occupied graves count
     */
    public function getOccupiedGravesCountAttribute(): int
    {
        return $this->graves()->where('status', 'occupied')->count();
    }

    /**
     * Get occupancy percentage
     */
    public function getOccupancyPercentageAttribute(): float
    {
        $total = $this->graves()->count();
        if ($total === 0) {
            return 0;
        }

        return ($this->occupied_graves_count / $total) * 100;
    }

    /**
     * Scope: only active cemeteries
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
