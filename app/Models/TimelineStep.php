<?php

namespace App\Models;

use App\Infrastructure\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TimelineStep extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'agency_id',
        'name',
        'description',
        'order',
        'is_required',
        'estimated_duration_hours',
        'required_documents',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'required_documents' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($step) {
            if (empty($step->uuid)) {
                $step->uuid = (string) Str::uuid();
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
     * Relazione: step usati in funeral timelines
     */
    public function funeralTimelines()
    {
        return $this->hasMany(FuneralTimeline::class);
    }

    /**
     * Scope: solo step obbligatori
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope: ordinati
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
