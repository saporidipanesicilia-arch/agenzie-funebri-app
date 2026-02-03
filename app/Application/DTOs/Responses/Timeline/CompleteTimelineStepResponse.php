<?php

namespace App\Application\DTOs\Responses\Timeline;

/**
 * Response DTO for completed timeline step.
 */
final readonly class CompleteTimelineStepResponse
{
    public function __construct(
        public int $timelineStepId,
        public string $stepName,
        public string $status,           // 'completed'
        public string $completedAt,
        public ?string $duration,        // Human-readable duration (e.g., "2 hours")
    ) {
    }

    public function toArray(): array
    {
        return [
            'timeline_step_id' => $this->timelineStepId,
            'step_name' => $this->stepName,
            'status' => $this->status,
            'completed_at' => $this->completedAt,
            'duration' => $this->duration,
        ];
    }
}
