<?php

namespace App\Application\DTOs\Responses\Funeral;

/**
 * Response DTO for resource assignment confirmation.
 */
final readonly class AssignResourcesToFuneralResponse
{
    public function __construct(
        public int $funeralId,
        public array $assignedUsers,     // Array of user details
        public array $assignedVehicles,  // Array of vehicle details
        public string $assignedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'funeral_id' => $this->funeralId,
            'assigned_users' => $this->assignedUsers,
            'assigned_vehicles' => $this->assignedVehicles,
            'assigned_at' => $this->assignedAt,
        ];
    }
}
