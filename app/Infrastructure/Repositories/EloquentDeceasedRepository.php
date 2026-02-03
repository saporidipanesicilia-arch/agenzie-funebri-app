<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\DeceasedRepositoryInterface;
use App\Models\Deceased;

/**
 * Eloquent implementation of DeceasedRepositoryInterface.
 */
class EloquentDeceasedRepository implements DeceasedRepositoryInterface
{
    public function create(array $data, int $tenantId): Deceased
    {
        // Ensure tenant_id is set
        $data['agency_id'] = $tenantId;

        return Deceased::create($data);
    }

    public function findById(int $deceasedId, int $tenantId): Deceased
    {
        return Deceased::where('agency_id', $tenantId)
            ->findOrFail($deceasedId);
    }
}
