<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Burial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'grave_id',
        'deceased_id',
        'funeral_id',
        'burial_date',
        'burial_type',
        'deceased_name',
        'death_date',
        'notes',
    ];

    protected $casts = [
        'burial_date' => 'date',
        'death_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($burial) {
            if (empty($burial->uuid)) {
                $burial->uuid = (string) Str::uuid();
            }
        });

        // Update grave status when burial is created
        static::created(function ($burial) {
            $grave = $burial->grave;
            $grave->increment('current_burials');

            if ($grave->current_burials >= $grave->max_burials) {
                $grave->update(['status' => 'occupied']);
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
     * Relazione: tomba
     */
    public function grave()
    {
        return $this->belongsTo(Grave::class);
    }

    /**
     * Relazione: defunto (se collegato)
     */
    public function deceased()
    {
        return $this->belongsTo(Deceased::class);
    }

    /**
     * Relazione: funerale (se collegato)
     */
    public function funeral()
    {
        return $this->belongsTo(Funeral::class);
    }

    /**
     * Relazione: concessione
     */
    public function concession()
    {
        return $this->hasOne(Concession::class);
    }

    /**
     * Get deceased name (from deceased or manual field)
     */
    public function getDeceasedFullNameAttribute(): string
    {
        if ($this->deceased) {
            return $this->deceased->full_name;
        }

        return $this->deceased_name ?? 'Non disponibile';
    }

    /**
     * Get burial type label
     */
    public function getBurialTypeLabelAttribute(): string
    {
        return match ($this->burial_type) {
            'inhumation' => 'Inumazione (terra)',
            'entombment' => 'Tumulazione (loculo)',
            'cremation_urn' => 'Urna ceneri',
            default => 'Altro',
        };
    }

    /**
     * Scope: by burial date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('burial_date', [$startDate, $endDate]);
    }

    /**
     * Scope: recent burials (last 30 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('burial_date', '>=', now()->subDays(30));
    }
}
