<?php

namespace App\Application\DTOs\Requests\Timeline;

/**
 * Request DTO for completing a timeline step.
 *
 * Represents the action of marking a single timeline step as completed.
 */
final readonly class CompleteTimelineStepRequest
{
    public function __construct(
        public int $tenantId,
        public int $funeralId,
        public int $timelineStepId,      // ID of FuneralTimeline record
        public ?string $completionNotes = null,
    ) {
    }

    public static function fromArray(int $tenantId, array $data): self
    {
        return new self(
            tenantId: $tenantId,
            funeralId: $data['funeral_id'],
            timelineStepId: $data['timeline_step_id'],
            completionNotes: $data['completion_notes'] ?? null,
        );
    }
}
