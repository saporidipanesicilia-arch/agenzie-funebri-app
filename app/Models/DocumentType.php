<?php

namespace App\Models;

use App\Infrastructure\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocumentType extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'agency_id',
        'name',
        'description',
        'is_required',
        'required_for_service_types',
        'max_file_size_mb',
        'allowed_extensions',
        'expiry_days',
        'template_fields',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'required_for_service_types' => 'array',
        'allowed_extensions' => 'array',
        'template_fields' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($docType) {
            if (empty($docType->uuid)) {
                $docType->uuid = (string) Str::uuid();
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
     * Relazione: documenti di questo tipo
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Check if this document type is required for a service type
     */
    public function isRequiredFor(string $serviceType): bool
    {
        if (!$this->is_required) {
            return false;
        }

        if (empty($this->required_for_service_types)) {
            return true; // Required for all if array is empty
        }

        return in_array($serviceType, $this->required_for_service_types);
    }

    /**
     * Scope: only required document types
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope: required for specific service type
     */
    public function scopeRequiredForServiceType($query, string $serviceType)
    {
        return $query->where('is_required', true)
            ->where(function ($q) use ($serviceType) {
                $q->whereJsonContains('required_for_service_types', $serviceType)
                    ->orWhereNull('required_for_service_types');
            });
    }

    /**
     * Validate file extension
     */
    public function isAllowedExtension(string $extension): bool
    {
        return in_array(strtolower($extension), $this->allowed_extensions ?? ['pdf']);
    }

    /**
     * Get max file size in bytes
     */
    public function getMaxFileSizeBytesAttribute(): int
    {
        return $this->max_file_size_mb * 1024 * 1024;
    }
}
