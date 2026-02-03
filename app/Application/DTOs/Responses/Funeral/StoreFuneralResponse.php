<?php

namespace App\Application\DTOs\Responses\Funeral;

use App\Models\Funeral;

/**
 * Response DTO for successful funeral creation.
 * 
 * Returns essential information about the newly created funeral
 * including timeline initialization and cost estimation.
 */
final readonly class StoreFuneralResponse
{
    public function __construct(
        public int $funeralId,
        public string $funeralCode,           // Human-readable code (e.g., "FUN-2026-001")
        public string $status,                // Initial status: 'draft'
        public array $timelineSteps,          // Array of initialized timeline steps
        public float $estimatedTotal,         // Sum of selected products
        public string $createdAt,             // ISO 8601 timestamp
    ) {
    }

    /**
     * Factory method to create response from Funeral model.
     */
    public static function fromFuneral(Funeral $funeral): self
    {
        return new self(
            funeralId: $funeral->id,
            funeralCode: $funeral->funeral_code,
            status: $funeral->status,
            timelineSteps: $funeral->timeline->map(fn($step) => [
                'id' => $step->id,
                'step_name' => $step->step_name,
                'status' => $step->status,
                'order' => $step->step_order,
            ])->toArray(),
            estimatedTotal: $funeral->activeQuote?->calculateTotal() ?? 0.0,
            createdAt: $funeral->created_at->toIso8601String(),
        );
    }

    /**
     * Convert to array for JSON response.
     */
    public function toArray(): array
    {
        return [
            'funeral_id' => $this->funeralId,
            'funeral_code' => $this->funeralCode,
            'status' => $this->status,
            'timeline_steps' => $this->timelineSteps,
            'estimated_total' => $this->estimatedTotal,
            'created_at' => $this->createdAt,
        ];
    }
}
