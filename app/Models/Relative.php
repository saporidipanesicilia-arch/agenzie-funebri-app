<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Relative extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'funeral_id',
        'name',
        'relation_type',
        'phone',
        'email',
        'is_primary_contact',
        'notes',
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($relative) {
            if (empty($relative->uuid)) {
                $relative->uuid = (string) Str::uuid();
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
     * Relazione: un parente appartiene a un funerale
     */
    public function funeral()
    {
        return $this->belongsTo(Funeral::class);
    }

    /**
     * Scope: solo contatti principali
     */
    public function scopePrimaryContacts($query)
    {
        return $query->where('is_primary_contact', true);
    }
}
