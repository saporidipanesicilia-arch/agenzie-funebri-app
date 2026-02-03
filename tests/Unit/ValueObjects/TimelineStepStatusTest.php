<?php

namespace Tests\Unit\ValueObjects;

use App\Domain\ValueObjects\TimelineStepStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TimelineStepStatusTest extends TestCase
{
    /** @test */
    public function it_allows_valid_transitions_from_pending()
    {
        $pending = TimelineStepStatus::PENDING;

        $this->assertTrue($pending->canTransitionTo(TimelineStepStatus::IN_PROGRESS));
        $this->assertTrue($pending->canTransitionTo(TimelineStepStatus::COMPLETED));
        $this->assertTrue($pending->canTransitionTo(TimelineStepStatus::SKIPPED));
    }

    /** @test */
    public function it_allows_in_progress_to_completed()
    {
        $inProgress = TimelineStepStatus::IN_PROGRESS;

        $this->assertTrue($inProgress->canTransitionTo(TimelineStepStatus::COMPLETED));
    }

    /** @test */
    public function it_rejects_completed_transitions()
    {
        $completed = TimelineStepStatus::COMPLETED;

        // Terminal state
        $this->assertFalse($completed->canTransitionTo(TimelineStepStatus::PENDING));
        $this->assertFalse($completed->canTransitionTo(TimelineStepStatus::IN_PROGRESS));
    }

    /** @test */
    public function it_rejects_skipped_transitions()
    {
        $skipped = TimelineStepStatus::SKIPPED;

        // Terminal state
        $this->assertFalse($skipped->canTransitionTo(TimelineStepStatus::PENDING));
        $this->assertFalse($skipped->canTransitionTo(TimelineStepStatus::COMPLETED));
    }

    /** @test */
    public function it_validates_transitions()
    {
        $inProgress = TimelineStepStatus::IN_PROGRESS;
        $pending = TimelineStepStatus::PENDING;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid timeline step status transition: in_progress → pending');

        $inProgress->validateTransition($pending); // Cannot go back
    }

    /** @test */
    public function it_checks_editability()
    {
        $this->assertTrue(TimelineStepStatus::PENDING->isEditable());
        $this->assertTrue(TimelineStepStatus::IN_PROGRESS->isEditable());
        $this->assertFalse(TimelineStepStatus::COMPLETED->isEditable());
        $this->assertFalse(TimelineStepStatus::SKIPPED->isEditable());
    }

    /** @test */
    public function it_checks_terminal_status()
    {
        $this->assertFalse(TimelineStepStatus::PENDING->isTerminal());
        $this->assertFalse(TimelineStepStatus::IN_PROGRESS->isTerminal());
        $this->assertTrue(TimelineStepStatus::COMPLETED->isTerminal());
        $this->assertTrue(TimelineStepStatus::SKIPPED->isTerminal());
    }

    /** @test */
    public function it_knows_when_timestamps_are_required()
    {
        // Start timestamp required when not pending
        $this->assertFalse(TimelineStepStatus::PENDING->requiresStartTimestamp());
        $this->assertTrue(TimelineStepStatus::IN_PROGRESS->requiresStartTimestamp());
        $this->assertTrue(TimelineStepStatus::COMPLETED->requiresStartTimestamp());

        // Completion timestamp only for completed
        $this->assertFalse(TimelineStepStatus::PENDING->requiresCompletionTimestamp());
        $this->assertFalse(TimelineStepStatus::IN_PROGRESS->requiresCompletionTimestamp());
        $this->assertTrue(TimelineStepStatus::COMPLETED->requiresCompletionTimestamp());
    }
}
