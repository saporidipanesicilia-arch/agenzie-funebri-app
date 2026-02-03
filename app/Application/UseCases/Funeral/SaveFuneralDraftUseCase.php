<?php

namespace App\Application\UseCases\Funeral;

use App\Application\DTOs\Requests\Funeral\SaveFuneralDraftRequest;
use App\Application\DTOs\Responses\Funeral\SaveFuneralDraftResponse;
use App\Models\FuneralDraft;
use Illuminate\Support\Facades\DB;

/**
 * Use Case: Save wizard draft progress.
 * 
 * Allows users to pause the wizard and resume later.
 * Drafts expire after 7 days.
 */
class SaveFuneralDraftUseCase
{
    public function execute(SaveFuneralDraftRequest $request): SaveFuneralDraftResponse
    {
        return DB::transaction(function () use ($request) {
            $expiresAt = now()->addDays(7);

            if ($request->draftId) {
                // Update existing draft
                $draft = FuneralDraft::where('agency_id', $request->tenantId)
                    ->findOrFail($request->draftId);

                $draft->update([
                    'wizard_data' => json_encode($request->wizardData),
                    'current_step' => $request->currentStep,
                    'expires_at' => $expiresAt,
                ]);
            } else {
                // Create new draft
                $draft = FuneralDraft::create([
                    'agency_id' => $request->tenantId,
                    'wizard_data' => json_encode($request->wizardData),
                    'current_step' => $request->currentStep,
                    'expires_at' => $expiresAt,
                ]);
            }

            return new SaveFuneralDraftResponse(
                draftId: $draft->id,
                expiresAt: $draft->expires_at->toIso8601String(),
            );
        });
    }
}
