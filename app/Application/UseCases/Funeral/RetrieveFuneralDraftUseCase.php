<?php

namespace App\Application\UseCases\Funeral;

use App\Application\DTOs\Responses\Funeral\RetrieveFuneralDraftResponse;
use App\Models\FuneralDraft;

/**
 * Use Case: Retrieve saved wizard draft.
 */
class RetrieveFuneralDraftUseCase
{
    public function execute(int $draftId, int $tenantId): RetrieveFuneralDraftResponse
    {
        $draft = FuneralDraft::where('agency_id', $tenantId)
            ->findOrFail($draftId);

        // Business Rule: Expired drafts cannot be retrieved
        if ($draft->expires_at < now()) {
            throw new \Exception('This draft has expired and can no longer be used.');
        }

        return new RetrieveFuneralDraftResponse(
            draftId: $draft->id,
            wizardData: json_decode($draft->wizard_data, true),
            currentStep: $draft->current_step,
            createdAt: $draft->created_at->toIso8601String(),
            expiresAt: $draft->expires_at->toIso8601String(),
        );
    }
}
