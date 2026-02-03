<?php

namespace App\Infrastructure\Traits;

use App\Infrastructure\Scopes\TenantScope;
use App\Models\Agency;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait BelongsToTenant
 * 
 * Aggiunge automaticamente il filtraggio per agency_id a tutti i model che lo usano.
 * Previene accidentali query cross-tenant.
 */
trait BelongsToTenant
{
    /**
     * Boot del trait
     */
    protected static function bootBelongsToTenant(): void
    {
        // Applica automaticamente il global scope per filtrare per agency_id
        static::addGlobalScope(new TenantScope);

        // Quando si crea un nuovo record, imposta automaticamente agency_id
        static::creating(function ($model) {
            if (!isset($model->agency_id) && auth()->check()) {
                $model->agency_id = auth()->user()->agency_id;
            }
        });
    }

    /**
     * Relazione con Agency
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
