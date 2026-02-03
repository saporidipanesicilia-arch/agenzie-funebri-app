<?php

namespace App\Domain\Repositories;

use App\Models\Funeral;

/**
 * Repository interface for Funeral entity operations.
 * 
 * This interface abstracts funeral persistence following the Repository pattern.
 * Implementations must enforce tenant isolation.
 */
interface FuneralRepositoryInterface
{
    /**
     * Create a new funeral within the given tenant context.
     */
    public function create(array $data, int $tenantId): Funeral;

    /**
     * Find a funeral by ID, scoped to tenant.
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $funeralId, int $tenantId): Funeral;

    /**
     * Check if a tax code already exists for an active funeral in tenant.
     */
    public function taxCodeExistsForActiveFuneral(string $taxCode, int $tenantId): bool;

    /**
     * Generate next funeral code for tenant.
     * Format: FUN-{YEAR}-{SEQUENCE}
     */
    public function generateFuneralCode(int $tenantId): string;
}
