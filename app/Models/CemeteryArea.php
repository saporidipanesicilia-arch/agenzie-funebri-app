<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CemeteryArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'cemetery_id',
        'name',
        'description',
        'area_type',
        'total_graves',
        'floor_level',
    ];

    protected $casts = [
        'total_graves' => 'integer',
        'floor_level' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($area) {
            if (empty($area->uuid)) {
                $area->uuid = (string) Str::uuid();
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
     * Relazione: appartiene a un cimitero
     */
    public function cemetery()
    {
        return $this->belongsTo(Cemetery::class);
    }

    /**
     * Relazione: tombe in questa area
     */
    public function graves()
    {
        return $this->hasMany(Grave::class);
    }

    /**
     * Get available graves in this area
     */
    public function getAvailableGravesAttribute()
    {
        return $this->graves()->where('status', 'available')->get();
    }

    /**
     * Get area type label
     */
    public function getAreaTypeLabelAttribute(): string
    {
        return match ($this->area_type) {
            'ground' => 'Tomba di terra',
            'wall' => 'Colombario (loculo a muro)',
            'chapel' => 'Cappella privata',
            'ossuary' => 'Ossario',
            default => 'Altro',
        };
    }

    /**
     * Scope: by area type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('area_type', $type);
    }
}
