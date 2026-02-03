<?php

namespace App\Models;

use App\Infrastructure\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Branch extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'agency_id',
        'name',
        'address',
        'city',
        'postal_code',
        'phone',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($branch) {
            if (empty($branch->uuid)) {
                $branch->uuid = (string) Str::uuid();
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
     * Relazione: una sede appartiene a un'agenzia
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Relazione: una sede ha molti utenti
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
