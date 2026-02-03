<?php

namespace App\Application\DTOs\Requests\Funeral;

/**
 * Request DTO for assigning resources to a funeral.
 *
 * Resources include staff members (operators, drivers) and vehicles (hearses).
 * This enables proper resource planning and availability tracking.
 */
final readonly class AssignResourcesToFuneralRequest
{
    public function __construct(
        public int $tenantId,
        public int $funeralId,
        public array $userIds,           // Staff members to assign (e.g., [1, 2, 3])
        public array $vehicleIds,        // Vehicles to assign (e.g., [5, 6])
        public ?string $assignmentNotes = null,
    ) {
    }

    public static function fromArray(int $tenantId, array $data): self
    {
        return new self(
            tenantId: $tenantId,
            funeralId: $data['funeral_id'],
            userIds: $data['user_ids'] ?? [],
            vehicleIds: $data['vehicle_ids'] ?? [],
            assignmentNotes: $data['assignment_notes'] ?? null,
        );
    }
}
