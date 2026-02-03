<?php

namespace App\Infrastructure\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantScope
 * 
 * Global scope che filtra automaticamente tutte le query per agency_id.
 * Questo previene accidentali data leaks tra tenant.
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Solo se l'utente è autenticato
        if (auth()->check()) {
            $builder->where($model->getTable() . '.agency_id', auth()->user()->agency_id);
        }
    }
}
