<?php

namespace App\Application\DTOs\Requests\Funeral;

/**
 * Request DTO for saving wizard draft progress.
 * 
 * Allows users to pause the wizard and resume later.
 * Drafts expire after 7 days.
 */
final readonly class SaveFuneralDraftRequest
{
    public function __construct(
        public int $tenantId,
        public ?int $draftId,                 // null for new draft, int for update
        public array $wizardData,             // Complete form state as JSON
        public int $currentStep,              // Step number 1-5
    ) {
    }

    /**
     * Factory method from validated HTTP data.
     */
    public static function fromArray(int $tenantId, array $data): self
    {
        return new self(
            tenantId: $tenantId,
            draftId: $data['draft_id'] ?? null,
            wizardData: $data['wizard_data'],
            currentStep: $data['current_step'],
        );
    }
}
