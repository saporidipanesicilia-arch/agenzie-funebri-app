<?php

namespace App\Application\DTOs\Requests\Cemetery;

/**
 * Request DTO for registering a death in the cemetery register.
 *
 * This creates the official cemetery record linking a deceased person
 * to their final resting place (grave, niche, ossuary, or columbarium).
 */
final readonly class RegisterCemeteryDeathRequest
{
    public function __construct(
        public int $tenantId,
        public int $funeralId,
        public int $graveId,
        public string $registrationNumber,  // Official cemetery register number
        public string $intermentDate,       // Date of burial/placement (ISO format)
        public int $concessionYears,        // Duration of concession (10, 20, 30, 99=perpetual)
        public ?string $registrationNotes = null,
    ) {
    }

    public static function fromArray(int $tenantId, array $data): self
    {
        return new self(
            tenantId: $tenantId,
            funeralId: $data['funeral_id'],
            graveId: $data['grave_id'],
            registrationNumber: $data['registration_number'],
            intermentDate: $data['interment_date'],
            concessionYears: $data['concession_years'],
            registrationNotes: $data['registration_notes'] ?? null,
        );
    }
}
