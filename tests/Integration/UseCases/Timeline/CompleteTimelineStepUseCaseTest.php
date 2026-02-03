<?php

namespace Tests\Integration\UseCases\Timeline;

use App\Application\DTOs\Requests\Timeline\CompleteTimelineStepRequest;
use App\Application\UseCases\Timeline\CompleteTimelineStepUseCase;
use App\Models\Funeral;
use App\Models\FuneralTimeline;
use App\Models\TimelineStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration Tests: CompleteTimelineStep Use Case
 *
 * Focuses on:
 * - Status progression (pending → in_progress → completed)
 * - Editability rules
 * - Multi-tenant isolation
 * - Automatic timestamp management
 * - Transaction boundaries
 */
class CompleteTimelineStepUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private CompleteTimelineStepUseCase $useCase;
    private User $tenantUser;
    private int $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(CompleteTimelineStepUseCase::class);

        $this->tenantUser = User::factory()->create([
            'agency_id' => 1,
            'role' => 'operator',
        ]);
        $this->tenantId = $this->tenantUser->agency_id;
    }

    /** @test */
    public function it_completes_pending_timeline_step_successfully()
    {
        // Arrange
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);
        $templateStep = TimelineStep::factory()->create([
            'agency_id' => $this->tenantId,
            'step_name' => 'Document Submission',
        ]);
        $timelineStep = FuneralTimeline::factory()->create([
            'funeral_id' => $funeral->id,
            'timeline_step_id' => $templateStep->id,
            'status' => 'pending',
        ]);

        $request = new CompleteTimelineStepRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            timelineStepId: $timelineStep->id,
            completionNotes: 'All documents submitted to municipality',
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Response is correct
        $this->assertEquals($timelineStep->id, $response->timelineStepId);
        $this->assertEquals('Document Submission', $response->stepName);
        $this->assertEquals('completed', $response->status);
        $this->assertNotNull($response->completedAt);

        // Assert: Database updated
        $timelineStep->refresh();
        $this->assertEquals('completed', $timelineStep->status);
        $this->assertNotNull($timelineStep->started_at); // Auto-set
        $this->assertNotNull($timelineStep->completed_at); // Auto-set
        $this->assertStringContainsString('All documents submitted', $timelineStep->notes);
    }

    /** @test */
    public function it_auto_starts_pending_step_before_completing()
    {
        // Arrange: Step is still pending
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);
        $templateStep = TimelineStep::factory()->create(['agency_id' => $this->tenantId]);
        $timelineStep = FuneralTimeline::factory()->create([
            'funeral_id' => $funeral->id,
            'timeline_step_id' => $templateStep->id,
            'status' => 'pending',
            'started_at' => null,
        ]);

        $request = new CompleteTimelineStepRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            timelineStepId: $timelineStep->id,
        );

        // Act
        $this->useCase->execute($request);

        // Assert: Step transitioned through in_progress before completing
        $timelineStep->refresh();
        $this->assertEquals('completed', $timelineStep->status);
        $this->assertNotNull($timelineStep->started_at); // Auto-set
    }

    /** @test */
    public function it_completes_in_progress_step_successfully()
    {
        // Arrange: Step already in progress
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);
        $templateStep = TimelineStep::factory()->create(['agency_id' => $this->tenantId]);
        $timelineStep = FuneralTimeline::factory()->create([
            'funeral_id' => $funeral->id,
            'timeline_step_id' => $templateStep->id,
            'status' => 'in_progress',
            'started_at' => now()->subHours(2),
        ]);

        $request = new CompleteTimelineStepRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            timelineStepId: $timelineStep->id,
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert
        $timelineStep->refresh();
        $this->assertEquals('completed', $timelineStep->status);
        $this->assertNotNull($response->duration); // Should calculate duration
    }

    /** @test */
    public function it_prevents_completing_already_completed_step()
    {
        // Arrange: Step already completed
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);
        $templateStep = TimelineStep::factory()->create(['agency_id' => $this->tenantId]);
        $timelineStep = FuneralTimeline::factory()->create([
            'funeral_id' => $funeral->id,
            'timeline_step_id' => $templateStep->id,
            'status' => 'completed',
            'completed_at' => now()->subDay(),
        ]);

        $request = new CompleteTimelineStepRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            timelineStepId: $timelineStep->id,
        );

        // Act & Assert: Domain rule violation
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('already completed');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_prevents_completing_skipped_step()
    {
        // Arrange: Step was skipped
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);
        $templateStep = TimelineStep::factory()->create(['agency_id' => $this->tenantId]);
        $timelineStep = FuneralTimeline::factory()->create([
            'funeral_id' => $funeral->id,
            'timeline_step_id' => $templateStep->id,
            'status' => 'skipped',
        ]);

        $request = new CompleteTimelineStepRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            timelineStepId: $timelineStep->id,
        );

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('already skipped');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_enforces_multi_tenant_isolation()
    {
        // Arrange: Funeral from different tenant
        $otherTenant = User::factory()->create(['agency_id' => 999]);
        $funeral = Funeral::factory()->create(['agency_id' => 999]);
        $templateStep = TimelineStep::factory()->create(['agency_id' => 999]);
        $timelineStep = FuneralTimeline::factory()->create([
            'funeral_id' => $funeral->id,
            'timeline_step_id' => $templateStep->id,
            'status' => 'pending',
        ]);

        $request = new CompleteTimelineStepRequest(
            tenantId: $this->tenantId, // Request from tenant 1
            funeralId: $funeral->id,    // But funeral belongs to tenant 999
            timelineStepId: $timelineStep->id,
        );

        // Act & Assert: Multi-tenant isolation
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_appends_notes_instead_of_overwriting()
    {
        // Arrange: Step with existing notes
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);
        $templateStep = TimelineStep::factory()->create(['agency_id' => $this->tenantId]);
        $timelineStep = FuneralTimeline::factory()->create([
            'funeral_id' => $funeral->id,
            'timeline_step_id' => $templateStep->id,
            'status' => 'pending',
            'notes' => 'Initial notes from assignment',
        ]);

        $request = new CompleteTimelineStepRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            timelineStepId: $timelineStep->id,
            completionNotes: 'Completion notes',
        );

        // Act
        $this->useCase->execute($request);

        // Assert: Notes appended, not overwritten
        $timelineStep->refresh();
        $this->assertStringContainsString('Initial notes from assignment', $timelineStep->notes);
        $this->assertStringContainsString('Completion notes', $timelineStep->notes);
    }

    /** @test */
    public function it_calculates_duration_correctly()
    {
        // Arrange: Step started 3 hours and 30 minutes ago
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);
        $templateStep = TimelineStep::factory()->create(['agency_id' => $this->tenantId]);
        $timelineStep = FuneralTimeline::factory()->create([
            'funeral_id' => $funeral->id,
            'timeline_step_id' => $templateStep->id,
            'status' => 'in_progress',
            'started_at' => now()->subHours(3)->subMinutes(30),
        ]);

        $request = new CompleteTimelineStepRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            timelineStepId: $timelineStep->id,
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Duration contains hours
        $this->assertStringContainsString('3h', $response->duration);
    }
}
