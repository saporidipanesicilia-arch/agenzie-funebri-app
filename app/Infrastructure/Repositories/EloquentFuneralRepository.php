<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\FuneralRepositoryInterface;
use App\Models\Funeral;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent implementation of FuneralRepositoryInterface.
 * 
 * Enforces tenant isolation via BelongsToTenant global scope.
 */
class EloquentFuneralRepository implements FuneralRepositoryInterface
{
    public function create(array $data, int $tenantId): Funeral
    {
        // Ensure tenant_id is set
        $data['agency_id'] = $tenantId;

        // Generate funeral code if not provided
        if (empty($data['funeral_code'])) {
            $data['funeral_code'] = $this->generateFuneralCode($tenantId);
        }

        return Funeral::create($data);
    }

    public function findById(int $funeralId, int $tenantId): Funeral
    {
        return Funeral::where('agency_id', $tenantId)
            ->findOrFail($funeralId);
    }

    public function taxCodeExistsForActiveFuneral(string $taxCode, int $tenantId): bool
    {
        return Funeral::where('agency_id', $tenantId)
            ->whereHas('deceased', function ($query) use ($taxCode) {
                $query->where('tax_code', $taxCode);
            })
            ->whereIn('status', ['active', 'planned', 'draft'])
            ->exists();
    }

    /**
     * Generate unique funeral code with format: FUN-YYYY-NNN
     *
     * Uses pessimistic locking to prevent race conditions under concurrent requests.
     */
    public function generateFuneralCode(int $tenantId): string
    {
        $year = now()->year;
        $prefix = "FUN-{$year}-";

        // Use pessimistic locking to ensure unique code generation
        $lastFuneral = Funeral::where('agency_id', $tenantId)
            ->where('funeral_code', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('funeral_code')
            ->first();

        if ($lastFuneral) {
            // Extract sequence from code like "FUN-2026-042"
            $parts = explode('-', $lastFuneral->funeral_code);
            $lastSequence = (int) ($parts[2] ?? 0);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        // Format: FUN-2026-001
        return sprintf('FUN-%d-%03d', $year, $nextSequence);
    }

}
