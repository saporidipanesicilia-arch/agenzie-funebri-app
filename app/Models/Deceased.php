<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Deceased extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'deceased'; // Plurale non standard

    protected $fillable = [
        'uuid',
        'funeral_id',
        'first_name',
        'last_name',
        'birth_date',
        'death_date',
        'place_of_birth',
        'place_of_death',
        'tax_code',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'death_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deceased) {
            if (empty($deceased->uuid)) {
                $deceased->uuid = (string) Str::uuid();
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
     * Get the full name of the deceased.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the age at death.
     */
    public function getAgeAtDeathAttribute(): ?int
    {
        if (!$this->birth_date || !$this->death_date) {
            return null;
        }

        return $this->birth_date->diffInYears($this->death_date);
    }

    /**
     * Relazione: un defunto appartiene a un funerale
     */
    public function funeral()
    {
        return $this->belongsTo(Funeral::class);
    }
}
