<?php

namespace App\Application\DTOs\Responses\Funeral;

/**
 * Response DTO for retrieving saved draft.
 */
final readonly class RetrieveFuneralDraftResponse
{
    public function __construct(
        public int $draftId,
        public array $wizardData,
        public int $currentStep,
        public string $createdAt,
        public string $expiresAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'draft_id' => $this->draftId,
            'wizard_data' => $this->wizardData,
            'current_step' => $this->currentStep,
            'created_at' => $this->createdAt,
            'expires_at' => $this->expiresAt,
        ];
    }
}
