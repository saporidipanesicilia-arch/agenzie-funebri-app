<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Timeline Step Status Value Object (Enum)
 *
 * Valid statuses: pending, in_progress, completed, skipped
 *
 * State transitions:
 * - pending → in_progress (user starts work)
 * - in_progress → completed (work finished)
 * - pending → skipped (optional step not needed)
 * - pending → completed (direct completion for simple steps)
 *
 * Immutable.
 */
enum TimelineStepStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case SKIPPED = 'skipped';

    /**
     * Check if transition to target status is allowed
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::PENDING => in_array($target, [self::IN_PROGRESS, self::COMPLETED, self::SKIPPED]),
            self::IN_PROGRESS => in_array($target, [self::COMPLETED]),
            self::COMPLETED => false, // Terminal state
            self::SKIPPED => false,   // Terminal state
        };
    }

    /**
     * Validate transition and throw if invalid
     */
    public function validateTransition(self $target): void
    {
        if (!$this->canTransitionTo($target)) {
            throw new InvalidArgumentException(
                "Invalid timeline step status transition: {$this->value} → {$target->value}"
            );
        }
    }

    /**
     * Check if step is editable
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::PENDING, self::IN_PROGRESS]);
    }

    /**
     * Check if step is terminal (cannot change)
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::SKIPPED]);
    }

    /**
     * Check if step requires timestamps
     */
    public function requiresStartTimestamp(): bool
    {
        return $this !== self::PENDING;
    }

    public function requiresCompletionTimestamp(): bool
    {
        return $this === self::COMPLETED;
    }
}
