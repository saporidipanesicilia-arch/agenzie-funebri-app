<?php

namespace App\Application\DTOs\Responses\Funeral;

/**
 * Response DTO for saved draft.
 */
final readonly class SaveFuneralDraftResponse
{
    public function __construct(
        public int $draftId,
        public string $expiresAt,             // ISO 8601 timestamp (7 days from creation)
    ) {
    }

    public function toArray(): array
    {
        return [
            'draft_id' => $this->draftId,
            'expires_at' => $this->expiresAt,
        ];
    }
}
