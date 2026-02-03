<?php

namespace App\Application\UseCases\Timeline;

use App\Application\DTOs\Requests\Timeline\CompleteTimelineStepRequest;
use App\Application\DTOs\Responses\Timeline\CompleteTimelineStepResponse;
use App\Models\FuneralTimeline;
use Illuminate\Support\Facades\DB;

/**
 * Use Case: Complete a Timeline Step
 *
 * ## Description
 * Marks a specific timeline step as completed for a funeral.
 * This represents the completion of a task in the funeral workflow
 * (e.g., "Document Submission", "Family Meeting", "Ceremony Preparation").
 *
 * ## Business Rules
 * 1. Step must exist and belong to the specified funeral
 * 2. Step must belong to the authenticated tenant
 * 3. Step cannot already be completed or skipped (idempotence check)
 * 4. Step must be in 'pending' or 'in_progress' status
 * 5. Completion timestamp is auto-set by model event
 *
 * ## Steps
 * 1. Validate step exists and is editable
 * 2. Update status to 'completed'
 * 3. Add completion notes if provided
 * 4. Model event auto-sets `completed_at` timestamp
 *
 * ## Domain Validations
 * - Tenant ownership (funeral must belong to tenant)
 * - Step editability (not already completed/skipped)
 * - Step progression logic (can only complete if not blocked)
 */
class CompleteTimelineStepUseCase
{
    /**
     * Execute the use case.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function execute(CompleteTimelineStepRequest $request): CompleteTimelineStepResponse
    {
        return DB::transaction(function () use ($request) {
            // Retrieve timeline step with tenant validation
            $timelineStep = FuneralTimeline::whereHas('funeral', function ($query) use ($request) {
                $query->where('agency_id', $request->tenantId)
                    ->where('id', $request->funeralId);
            })
                ->findOrFail($request->timelineStepId);

            // Business Rule: Step must be editable
            if (!$timelineStep->isEditable()) {
                throw new \Exception(
                    'This timeline step cannot be completed because it is already ' . $timelineStep->status
                );
            }

            // Business Rule: Auto-start if still pending
            if ($timelineStep->status === 'pending') {
                $timelineStep->update(['status' => 'in_progress']);
            }

            // Complete the step
            $timelineStep->update([
                'status' => 'completed',
                'notes' => $request->completionNotes
                    ? ($timelineStep->notes ? $timelineStep->notes . "\n\n" . $request->completionNotes : $request->completionNotes)
                    : $timelineStep->notes,
                // completed_at is auto-set by model event
            ]);

            // Reload to get fresh timestamps
            $timelineStep->refresh();

            // Calculate human-readable duration
            $durationText = null;
            if ($timelineStep->started_at && $timelineStep->completed_at) {
                $hours = $timelineStep->started_at->diffInHours($timelineStep->completed_at);
                $minutes = $timelineStep->started_at->diffInMinutes($timelineStep->completed_at) % 60;

                if ($hours > 0) {
                    $durationText = "{$hours}h " . ($minutes > 0 ? "{$minutes}m" : '');
                } else {
                    $durationText = "{$minutes}m";
                }
            }

            return new CompleteTimelineStepResponse(
                timelineStepId: $timelineStep->id,
                stepName: $timelineStep->timelineStep->step_name,
                status: $timelineStep->status,
                completedAt: $timelineStep->completed_at->toIso8601String(),
                duration: $durationText,
            );
        });
    }
}
