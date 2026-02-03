<?php

namespace App\Application\DTOs\Requests\Funeral;

/**
 * Request DTO for storing a new funeral from the Timeline Wizard.
 * 
 * This DTO encapsulates all data collected through the 5-step wizard process:
 * - Step 1: Deceased biographical data
 * - Step 2: Ceremony type and details
 * - Step 3: Selected products from Memorial Table
 * - Step 4: Required documents checklist
 * - Step 5: Additional notes
 */
final readonly class StoreFuneralRequest
{
    public function __construct(
        // Tenant context (auto-injected from auth)
        public int $tenantId,

        // Step 1: Deceased Data
        public string $deceasedName,
        public string $deceasedSurname,
        public string $deceasedTaxCode,
        public string $deceasedBirthDate,      // ISO format: YYYY-MM-DD
        public string $deceasedBirthCity,
        public string $deceasedDeathDate,      // ISO format: YYYY-MM-DD HH:MM
        public string $deceasedDeathCity,

        // Step 2: Ceremony Configuration
        public string $ceremonyType,            // 'burial' | 'cremation'
        public ?string $ceremonyLocation,
        public ?string $ceremonyDate,           // ISO format: YYYY-MM-DD HH:MM

        // Step 3: Product Selection (Memorial Table)
        public array $productIds,               // Array of product IDs: [1, 5, 7]

        // Step 4: Document Checklist
        public array $requiredDocuments,        // Array of document types: ['certificate_death', 'cremation_request']

        // Optional fields
        public ?string $notes = null,
    ) {
    }

    /**
     * Factory method to create request from validated HTTP request data.
     */
    public static function fromArray(int $tenantId, array $data): self
    {
        return new self(
            tenantId: $tenantId,
            deceasedName: $data['deceased_name'],
            deceasedSurname: $data['deceased_surname'],
            deceasedTaxCode: $data['deceased_tax_code'],
            deceasedBirthDate: $data['deceased_birth_date'],
            deceasedBirthCity: $data['deceased_birth_city'],
            deceasedDeathDate: $data['deceased_death_date'],
            deceasedDeathCity: $data['deceased_death_city'],
            ceremonyType: $data['ceremony_type'],
            ceremonyLocation: $data['ceremony_location'] ?? null,
            ceremonyDate: $data['ceremony_date'] ?? null,
            productIds: $data['product_ids'] ?? [],
            requiredDocuments: $data['required_documents'] ?? [],
            notes: $data['notes'] ?? null,
        );
    }
}
