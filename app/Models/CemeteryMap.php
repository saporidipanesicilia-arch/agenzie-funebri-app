<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CemeteryMap extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'cemetery_id',
        'name',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size_kb',
        'order',
    ];

    protected $casts = [
        'file_size_kb' => 'integer',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($map) {
            if (empty($map->uuid)) {
                $map->uuid = (string) Str::uuid();
            }
        });

        // Delete file when map is deleted
        static::deleting(function ($map) {
            if ($map->file_path && Storage::exists($map->file_path)) {
                Storage::delete($map->file_path);
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
     * Get file URL
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        $kb = $this->file_size_kb;

        if ($kb < 1024) {
            return $kb . ' KB';
        }

        return round($kb / 1024, 2) . ' MB';
    }

    /**
     * Scope: ordered maps
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
