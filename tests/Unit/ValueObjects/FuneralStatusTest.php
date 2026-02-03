<?php

namespace Tests\Unit\ValueObjects;

use App\Domain\ValueObjects\FuneralStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FuneralStatusTest extends TestCase
{
    /** @test */
    public function it_allows_valid_transitions()
    {
        $draft = FuneralStatus::DRAFT;
        $active = FuneralStatus::ACTIVE;
        $completed = FuneralStatus::COMPLETED;
        $closed = FuneralStatus::CLOSED;

        // Valid transitions
        $this->assertTrue($draft->canTransitionTo($active));
        $this->assertTrue($active->canTransitionTo($completed));
        $this->assertTrue($completed->canTransitionTo($closed));
    }

    /** @test */
    public function it_rejects_invalid_transitions()
    {
        $draft = FuneralStatus::DRAFT;
        $completed = FuneralStatus::COMPLETED;

        // Cannot skip states
        $this->assertFalse($draft->canTransitionTo($completed));
    }

    /** @test */
    public function it_rejects_archived_transitions()
    {
        $archived = FuneralStatus::ARCHIVED;

        // Terminal state - no transitions allowed
        $this->assertFalse($archived->canTransitionTo(FuneralStatus::DRAFT));
        $this->assertFalse($archived->canTransitionTo(FuneralStatus::ACTIVE));
    }

    /** @test */
    public function it_validates_transitions()
    {
        $draft = FuneralStatus::DRAFT;
        $completed = FuneralStatus::COMPLETED;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid funeral status transition: draft → completed');

        $draft->validateTransition($completed);
    }

    /** @test */
    public function it_checks_editability()
    {
        $this->assertTrue(FuneralStatus::DRAFT->isEditable());
        $this->assertTrue(FuneralStatus::ACTIVE->isEditable());
        $this->assertFalse(FuneralStatus::COMPLETED->isEditable());
        $this->assertFalse(FuneralStatus::CLOSED->isEditable());
    }

    /** @test */
    public function it_checks_finalized_state()
    {
        $this->assertFalse(FuneralStatus::DRAFT->isFinalized());
        $this->assertFalse(FuneralStatus::ACTIVE->isFinalized());
        $this->assertTrue(FuneralStatus::COMPLETED->isFinalized());
        $this->assertTrue(FuneralStatus::CLOSED->isFinalized());
        $this->assertTrue(FuneralStatus::ARCHIVED->isFinalized());
    }
}
