<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Grave extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'cemetery_area_id',
        'grave_number',
        'grave_type',
        'status',
        'row',
        'column',
        'max_burials',
        'current_burials',
        'notes',
    ];

    protected $casts = [
        'row' => 'integer',
        'column' => 'integer',
        'max_burials' => 'integer',
        'current_burials' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($grave) {
            if (empty($grave->uuid)) {
                $grave->uuid = (string) Str::uuid();
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
     * Relazione: appartiene a un'area cimiteriale
     */
    public function cemeteryArea()
    {
        return $this->belongsTo(CemeteryArea::class);
    }

    /**
     * Relazione: sepolture in questa tomba
     */
    public function burials()
    {
        return $this->hasMany(Burial::class);
    }

    /**
     * Relazione: concessioni per questa tomba
     */
    public function concessions()
    {
        return $this->hasMany(Concession::class);
    }

    /**
     * Get active concession
     */
    public function getActiveConcessionAttribute()
    {
        return $this->concessions()
            ->where('status', 'active')
            ->orWhere('status', 'expiring')
            ->latest()
            ->first();
    }

    /**
     * Check if grave is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->current_burials < $this->max_burials;
    }

    /**
     * Check if grave is full
     */
    public function isFull(): bool
    {
        return $this->current_burials >= $this->max_burials;
    }

    /**
     * Get grave type label
     */
    public function getGraveTypeLabelAttribute(): string
    {
        return match ($this->grave_type) {
            'loculo' => 'Loculo',
            'tomba_famiglia' => 'Tomba di famiglia',
            'campo_comune' => 'Campo comune',
            'ossario' => 'Ossario',
            'celletta' => 'Celletta',
            'cappella' => 'Cappella privata',
            default => 'Altro',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available' => 'Disponibile',
            'occupied' => 'Occupato',
            'reserved' => 'Riservato',
            'maintenance' => 'In manutenzione',
            default => 'Sconosciuto',
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available' => 'green',
            'occupied' => 'gray',
            'reserved' => 'yellow',
            'maintenance' => 'orange',
            default => 'gray',
        };
    }

    /**
     * Scope: available graves
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->whereColumn('current_burials', '<', 'max_burials');
    }

    /**
     * Scope: occupied graves
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope: by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('grave_type', $type);
    }
}
