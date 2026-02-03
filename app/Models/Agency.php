<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'vat_number',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($agency) {
            if (empty($agency->uuid)) {
                $agency->uuid = (string) Str::uuid();
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
     * Relazione: un'agenzia ha molte sedi
     */
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Relazione: un'agenzia ha molti utenti
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
