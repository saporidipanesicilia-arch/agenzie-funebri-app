<?php

namespace App\Domain\Repositories;

use App\Models\Deceased;

/**
 * Repository interface for Deceased entity operations.
 */
interface DeceasedRepositoryInterface
{
    /**
     * Create a new deceased record within tenant context.
     */
    public function create(array $data, int $tenantId): Deceased;

    /**
     * Find deceased by ID, scoped to tenant.
     */
    public function findById(int $deceasedId, int $tenantId): Deceased;
}
