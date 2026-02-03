<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_access_token_id',
        'accessed_at',
        'ip_address',
        'user_agent',
        'accessed_resource',
        'action',
        'resource_id',
        'details',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
    ];

    /**
     * Relazione: token di accesso
     */
    public function accessToken()
    {
        return $this->belongsTo(FamilyAccessToken::class, 'family_access_token_id');
    }

    /**
     * Get details as array
     */
    public function getDetailsArrayAttribute(): ?array
    {
        if (!$this->details) {
            return null;
        }

        return json_decode($this->details, true);
    }

    /**
     * Get resource label
     */
    public function getResourceLabelAttribute(): string
    {
        return match ($this->accessed_resource) {
            'funeral' => 'Dettagli funerale',
            'timeline' => 'Timeline',
            'documents' => 'Elenco documenti',
            'document' => 'Documento',
            'quote' => 'Preventivo',
            'cemetery' => 'Info cimitero',
            default => 'Altro',
        };
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'view' => 'Visualizzato',
            'download' => 'Scaricato',
            default => 'Altro',
        };
    }

    /**
     * Scope: by resource
     */
    public function scopeByResource($query, string $resource)
    {
        return $query->where('accessed_resource', $resource);
    }

    /**
     * Scope: by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: recent (last 30 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('accessed_at', '>=', now()->subDays(30));
    }
}
