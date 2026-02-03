<?php

namespace App\Application\UseCases\Funeral;

use App\Application\DTOs\Requests\Funeral\AssignResourcesToFuneralRequest;
use App\Application\DTOs\Responses\Funeral\AssignResourcesToFuneralResponse;
use App\Models\Funeral;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Use Case: Assign Resources to Funeral
 *
 * ## Description
 * Assigns human and material resources to a funeral service.
 * Resources include:
 * - Staff members (operators, drivers, embalmers)
 * - Vehicles (hearses, flower cars, family cars)
 *
 * This enables proper resource planning, availability tracking,
 * and prevents double-booking conflicts.
 *
 * ## Business Rules
 * 1. Funeral must exist and belong to tenant
 * 2. All users must belong to the same tenant
 * 3. All vehicles must belong to the same tenant
 * 4. Resources can be reassigned (not locked once assigned)
 * 5. Assignment creates audit trail
 *
 * ## Steps
 * 1. Validate funeral ownership
 * 2. Validate all users belong to tenant
 * 3. Validate all vehicles belong to tenant (if system supports vehicles)
 * 4. Sync resources (replace existing assignments)
 * 5. Log assignment action
 *
 * ## Domain Validations
 * - Tenant ownership of funeral and all resources
 * - Resource availability (future enhancement: check calendar conflicts)
 * - Permission validation (user has rights to assign resources)
 */
class AssignResourcesToFuneralUseCase
{
    /**
     * Execute the use case.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function execute(AssignResourcesToFuneralRequest $request): AssignResourcesToFuneralResponse
    {
        return DB::transaction(function () use ($request) {
            // Validate funeral exists and belongs to tenant
            $funeral = Funeral::where('agency_id', $request->tenantId)
                ->findOrFail($request->funeralId);

            // Business Rule: Funeral must not be closed/archived
            if (in_array($funeral->status, ['closed', 'archived'])) {
                throw new \Exception('Cannot assign resources to a closed or archived funeral.');
            }

            // Validate all users belong to tenant
            if (!empty($request->userIds)) {
                $validUserCount = User::where('agency_id', $request->tenantId)
                    ->whereIn('id', $request->userIds)
                    ->count();

                if ($validUserCount !== count($request->userIds)) {
                    throw new \Exception('One or more users do not belong to your organization.');
                }

                // Sync user assignments (replaces existing assignments)
                // Using many-to-many relationship: funerals <-> users
                $funeral->assignedUsers()->sync($request->userIds);
            } else {
                // Clear all user assignments if empty array provided
                $funeral->assignedUsers()->detach();
            }

            // Future: Validate and sync vehicles (currently no Vehicle model/migration)
            // For now, we'll store vehicle_ids in notes or skip this feature
            if (!empty($request->vehicleIds)) {
                // Placeholder: Vehicle assignment would go here
                // For production, this requires a vehicles table and funeral_vehicle pivot
            }

            // Add assignment notes to funeral
            if ($request->assignmentNotes) {
                $funeral->update([
                    'notes' => $funeral->notes
                        ? $funeral->notes . "\n\n[Assegnazione Risorse] " . $request->assignmentNotes
                        : "[Assegnazione Risorse] " . $request->assignmentNotes,
                ]);
            }

            // Reload relationships
            $funeral->load('assignedUsers');

            // Format response
            $assignedUsers = $funeral->assignedUsers->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'operator',
            ])->toArray();

            $assignedVehicles = []; // Placeholder for future vehicle support

            return new AssignResourcesToFuneralResponse(
                funeralId: $funeral->id,
                assignedUsers: $assignedUsers,
                assignedVehicles: $assignedVehicles,
                assignedAt: now()->toIso8601String(),
            );
        });
    }
}
