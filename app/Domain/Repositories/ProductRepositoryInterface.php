<?php

namespace App\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * Get all active products for a tenant.
     */
    public function getActiveProducts(int $tenantId, ?string $category = null): Collection;

    /**
     * Find a product by ID (scoped to tenant).
     */
    public function findById(int $id, int $tenantId);

    /**
     * Find multiple products by IDs (scoped to tenant).
     */
    public function findMany(array $ids, int $tenantId): Collection;
}
