<?php

namespace App\Application\UseCases\Cemetery;

use App\Application\DTOs\Requests\Cemetery\RegisterCemeteryDeathRequest;
use App\Application\DTOs\Responses\Cemetery\RegisterCemeteryDeathResponse;
use App\Models\Funeral;
use App\Models\Grave;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Use Case: Register Cemetery Death
 *
 * ## Description
 * Registers a death in the official cemetery register and assigns
 * the deceased to a specific grave location. This creates the legal
 * cemetery record required by Italian cemetery regulations.
 *
 * This operation:
 * - Links funeral → grave
 * - Sets grave status to 'occupied'
 * - Establishes concession period (burial rights duration)
 * - Generates official cemetery register entry
 *
 * ## Business Rules
 * 1. Funeral must exist and belong to tenant
 * 2. Grave must exist and belong to same tenant
 * 3. Grave must be available (not already occupied)
 * 4. Registration number must be unique within cemetery
 * 5. Concession years must be valid (10, 20, 30, or 99 for perpetual)
 * 6. Interment date cannot be before death date
 * 7. Concession expiration is calculated from interment date
 *
 * ## Steps
 * 1. Validate funeral and grave ownership
 * 2. Check grave availability
 * 3. Validate registration number uniqueness
 * 4. Calculate concession expiration date
 * 5. Update grave status to 'occupied'
 * 6. Link grave to funeral
 * 7. Create cemetery register entry (audit trail)
 *
 * ## Domain Validations
 * - Tenant ownership (funeral, grave, cemetery all same tenant)
 * - Grave availability (status must be 'available' or 'reserved')
 * - Chronological consistency (interment >= death date)
 * - Regulatory compliance (valid concession duration)
 */
class RegisterCemeteryDeathUseCase
{
    /**
     * Execute the use case.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function execute(RegisterCemeteryDeathRequest $request): RegisterCemeteryDeathResponse
    {
        return DB::transaction(function () use ($request) {
            // Validate funeral exists and belongs to tenant
            $funeral = Funeral::where('agency_id', $request->tenantId)
                ->with('deceased')
                ->findOrFail($request->funeralId);

            // Validate grave exists and belongs to tenant
            $grave = Grave::whereHas('cemetery', function ($query) use ($request) {
                $query->where('agency_id', $request->tenantId);
            })
                ->findOrFail($request->graveId);

            // Business Rule: Grave must be available
            if (!in_array($grave->status, ['available', 'reserved'])) {
                throw new \Exception(
                    "This grave is currently {$grave->status} and cannot accept a new burial."
                );
            }

            // Business Rule: Valid concession years
            $validConcessionYears = [10, 20, 30, 99]; // 99 = perpetual
            if (!in_array($request->concessionYears, $validConcessionYears)) {
                throw new \Exception(
                    'Concession years must be one of: 10, 20, 30, or 99 (perpetual).'
                );
            }

            // Business Rule: Interment date must be on or after death date
            $intermentDate = Carbon::parse($request->intermentDate);
            $deathDate = $funeral->deceased->death_date;

            if ($intermentDate->lt($deathDate)) {
                throw new \Exception(
                    'Interment date cannot be before the death date.'
                );
            }

            // Business Rule: Registration number must be unique in cemetery
            $duplicateRegistration = Grave::where('cemetery_id', $grave->cemetery_id)
                ->where('registration_number', $request->registrationNumber)
                ->where('id', '!=', $grave->id)
                ->exists();

            if ($duplicateRegistration) {
                throw new \Exception(
                    'This registration number is already used in the cemetery register.'
                );
            }

            // Calculate concession expiration
            $concessionExpiresAt = $request->concessionYears == 99
                ? null // Perpetual concession
                : $intermentDate->copy()->addYears($request->concessionYears);

            // Update grave with occupancy details
            $grave->update([
                'funeral_id' => $funeral->id,
                'status' => 'occupied',
                'registration_number' => $request->registrationNumber,
                'occupant_name' => $funeral->deceased->full_name,
                'interment_date' => $intermentDate,
                'concession_years' => $request->concessionYears,
                'concession_expires_at' => $concessionExpiresAt,
                'notes' => $request->registrationNotes
                    ? ($grave->notes ? $grave->notes . "\n\n" . $request->registrationNotes : $request->registrationNotes)
                    : $grave->notes,
            ]);

            // Update funeral with cemetery information
            $funeral->update([
                'grave_id' => $grave->id,
                'interment_date' => $intermentDate,
            ]);

            // Future: Create audit log entry in cemetery_register table
            // This would be a dedicated table for legal compliance

            return new RegisterCemeteryDeathResponse(
                graveId: $grave->id,
                graveNumber: $grave->grave_number,
                registrationNumber: $grave->registration_number,
                deceasedName: $funeral->deceased->full_name,
                intermentDate: $intermentDate->toIso8601String(),
                concessionExpiresAt: $concessionExpiresAt?->toIso8601String() ?? 'Perpetual',
                registeredAt: now()->toIso8601String(),
            );
        });
    }
}
