<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Funeral Status Value Object (Enum)
 *
 * Valid statuses: draft, active, completed, closed, archived
 *
 * State transitions:
 * - draft → active (funeral starts)
 * - active → completed (all timeline done)
 * - completed → closed (administrative closure)
 * - closed → archived (long-term storage)
 *
 * Immutable.
 */
enum FuneralStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';

    /**
     * Check if transition to target status is allowed
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::DRAFT => in_array($target, [self::ACTIVE, self::ARCHIVED]),
            self::ACTIVE => in_array($target, [self::COMPLETED, self::ARCHIVED]),
            self::COMPLETED => in_array($target, [self::CLOSED]),
            self::CLOSED => in_array($target, [self::ARCHIVED]),
            self::ARCHIVED => false, // Terminal state
        };
    }

    /**
     * Validate transition and throw if invalid
     */
    public function validateTransition(self $target): void
    {
        if (!$this->canTransitionTo($target)) {
            throw new InvalidArgumentException(
                "Invalid funeral status transition: {$this->value} → {$target->value}"
            );
        }
    }

    /**
     * Check if funeral is editable in this status
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::ACTIVE]);
    }

    /**
     * Check if funeral is active (in progress)
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if funeral is finalized (cannot be modified)
     */
    public function isFinalized(): bool
    {
        return in_array($this, [self::COMPLETED, self::CLOSED, self::ARCHIVED]);
    }
}
