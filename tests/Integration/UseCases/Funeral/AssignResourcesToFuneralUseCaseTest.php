<?php

namespace Tests\Integration\UseCases\Funeral;

use App\Application\DTOs\Requests\Funeral\AssignResourcesToFuneralRequest;
use App\Application\UseCases\Funeral\AssignResourcesToFuneralUseCase;
use App\Models\Funeral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration Tests: AssignResourcesToFuneral Use Case
 *
 * Focuses on:
 * - Multi-tenant validation for users
 * - Funeral status checks
 * - Sync behavior (replace not add)
 * - Transaction rollback on error
 */
class AssignResourcesToFuneralUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private AssignResourcesToFuneralUseCase $useCase;
    private User $tenantUser;
    private int $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(AssignResourcesToFuneralUseCase::class);

        $this->tenantUser = User::factory()->create([
            'agency_id' => 1,
            'role' => 'admin',
        ]);
        $this->tenantId = $this->tenantUser->agency_id;
    }

    /** @test */
    public function it_assigns_users_to_funeral_successfully()
    {
        // Arrange
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'status' => 'planned',
        ]);

        $operator1 = User::factory()->create(['agency_id' => $this->tenantId, 'name' => 'Mario Rossi']);
        $operator2 = User::factory()->create(['agency_id' => $this->tenantId, 'name' => 'Luigi Verdi']);

        $request = new AssignResourcesToFuneralRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            userIds: [$operator1->id, $operator2->id],
            vehicleIds: [],
            assignmentNotes: 'Main ceremony team',
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Response contains assigned users
        $this->assertEquals($funeral->id, $response->funeralId);
        $this->assertCount(2, $response->assignedUsers);
        $this->assertEquals('Mario Rossi', $response->assignedUsers[0]['name']);
        $this->assertEquals('Luigi Verdi', $response->assignedUsers[1]['name']);

        // Assert: Database relationship created
        $funeral->refresh();
        $this->assertCount(2, $funeral->assignedUsers);
    }

    /** @test */
    public function it_replaces_existing_assignments_using_sync()
    {
        // Arrange: Funeral with existing assignment
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);

        $oldOperator = User::factory()->create(['agency_id' => $this->tenantId]);
        $newOperator = User::factory()->create(['agency_id' => $this->tenantId]);

        // Initial assignment
        $funeral->assignedUsers()->attach($oldOperator->id);
        $this->assertCount(1, $funeral->assignedUsers);

        $request = new AssignResourcesToFuneralRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            userIds: [$newOperator->id], // Replace with new operator
            vehicleIds: [],
        );

        // Act
        $this->useCase->execute($request);

        // Assert: Old assignment replaced, not additive
        $funeral->refresh();
        $this->assertCount(1, $funeral->assignedUsers);
        $this->assertEquals($newOperator->id, $funeral->assignedUsers->first()->id);
    }

    /** @test */
    public function it_clears_all_assignments_when_empty_array_provided()
    {
        // Arrange: Funeral with existing assignments
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);
        $operator = User::factory()->create(['agency_id' => $this->tenantId]);
        $funeral->assignedUsers()->attach($operator->id);

        $request = new AssignResourcesToFuneralRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            userIds: [], // Empty array = clear all
            vehicleIds: [],
        );

        // Act
        $this->useCase->execute($request);

        // Assert: All assignments cleared
        $funeral->refresh();
        $this->assertCount(0, $funeral->assignedUsers);
    }

    /** @test */
    public function it_prevents_assigning_users_from_different_tenant()
    {
        // Arrange
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);

        $ownOperator = User::factory()->create(['agency_id' => $this->tenantId]);
        $foreignOperator = User::factory()->create(['agency_id' => 999]); // Different tenant!

        $request = new AssignResourcesToFuneralRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            userIds: [$ownOperator->id, $foreignOperator->id], // Mixed tenants
            vehicleIds: [],
        );

        // Act & Assert: Domain rule violation
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('do not belong to your organization');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_prevents_assignment_to_closed_funeral()
    {
        // Arrange: Closed funeral
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'status' => 'closed',
        ]);

        $operator = User::factory()->create(['agency_id' => $this->tenantId]);

        $request = new AssignResourcesToFuneralRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            userIds: [$operator->id],
            vehicleIds: [],
        );

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('closed or archived');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_prevents_assignment_to_archived_funeral()
    {
        // Arrange: Archived funeral
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'status' => 'archived',
        ]);

        $operator = User::factory()->create(['agency_id' => $this->tenantId]);

        $request = new AssignResourcesToFuneralRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            userIds: [$operator->id],
            vehicleIds: [],
        );

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('closed or archived');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_enforces_multi_tenant_isolation_for_funeral()
    {
        // Arrange: Funeral from different tenant
        $funeral = Funeral::factory()->create(['agency_id' => 999]);
        $operator = User::factory()->create(['agency_id' => $this->tenantId]);

        $request = new AssignResourcesToFuneralRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id, // Different tenant's funeral
            userIds: [$operator->id],
            vehicleIds: [],
        );

        // Act & Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_appends_assignment_notes_to_funeral()
    {
        // Arrange
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'notes' => 'Initial funeral notes',
        ]);

        $operator = User::factory()->create(['agency_id' => $this->tenantId]);

        $request = new AssignResourcesToFuneralRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            userIds: [$operator->id],
            vehicleIds: [],
            assignmentNotes: 'Team for ceremony on Feb 10',
        );

        // Act
        $this->useCase->execute($request);

        // Assert: Notes appended
        $funeral->refresh();
        $this->assertStringContainsString('Initial funeral notes', $funeral->notes);
        $this->assertStringContainsString('[Assegnazione Risorse]', $funeral->notes);
        $this->assertStringContainsString('Team for ceremony on Feb 10', $funeral->notes);
    }

    /** @test */
    public function it_rolls_back_on_validation_failure()
    {
        // Arrange: One valid user, one invalid
        $funeral = Funeral::factory()->create(['agency_id' => $this->tenantId]);
        $validUser = User::factory()->create(['agency_id' => $this->tenantId]);

        $request = new AssignResourcesToFuneralRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            userIds: [$validUser->id, 99999], // Invalid user ID
            vehicleIds: [],
        );

        // Act
        try {
            $this->useCase->execute($request);
        } catch (\Exception $e) {
            // Expected to fail
        }

        // Assert: Transaction rolled back - no partial assignment
        $funeral->refresh();
        $this->assertCount(0, $funeral->assignedUsers);
    }
}
