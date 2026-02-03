<?php

namespace App\Application\DTOs\Responses\Cemetery;

/**
 * Response DTO for cemetery death registration confirmation.
 */
final readonly class RegisterCemeteryDeathResponse
{
    public function __construct(
        public int $graveId,
        public string $graveNumber,
        public string $registrationNumber,
        public string $deceasedName,
        public string $intermentDate,
        public string $concessionExpiresAt,
        public string $registeredAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'grave_id' => $this->graveId,
            'grave_number' => $this->graveNumber,
            'registration_number' => $this->registrationNumber,
            'deceased_name' => $this->deceasedName,
            'interment_date' => $this->intermentDate,
            'concession_expires_at' => $this->concessionExpiresAt,
            'registered_at' => $this->registeredAt,
        ];
    }
}
