<?php

namespace Tests\Integration\UseCases\Cemetery;

use App\Application\DTOs\Requests\Cemetery\RegisterCemeteryDeathRequest;
use App\Application\UseCases\Cemetery\RegisterCemeteryDeathUseCase;
use App\Models\Cemetery;
use App\Models\CemeteryArea;
use App\Models\Deceased;
use App\Models\Funeral;
use App\Models\Grave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration Tests: RegisterCemeteryDeath Use Case
 *
 * Focuses on:
 * - Domain rule enforcement (grave availability, valid concessions, chronological consistency)
 * - Multi-tenant isolation
 * - Error scenarios (occupied graves, invalid dates, duplicate registry numbers)
 * - Concession expiration calculation
 */
class RegisterCemeteryDeathUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private RegisterCemeteryDeathUseCase $useCase;
    private User $tenantUser;
    private int $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(RegisterCemeteryDeathUseCase::class);

        // Create tenant context
        $this->tenantUser = User::factory()->create([
            'agency_id' => 1,
            'role' => 'operator',
        ]);
        $this->tenantId = $this->tenantUser->agency_id;
    }

    /** @test */
    public function it_registers_cemetery_death_with_valid_data()
    {
        // Arrange: Create cemetery infrastructure
        $cemetery = Cemetery::factory()->create(['agency_id' => $this->tenantId]);
        $area = CemeteryArea::factory()->create(['cemetery_id' => $cemetery->id]);
        $grave = Grave::factory()->create([
            'cemetery_area_id' => $area->id,
            'grave_number' => 'A-101',
            'status' => 'available',
        ]);

        // Create funeral with deceased
        $deceased = Deceased::factory()->create([
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'death_date' => Carbon::parse('2026-02-01'),
        ]);
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'funeral_id' => $deceased->id,
        ]);
        $deceased->update(['funeral_id' => $funeral->id]);

        $request = new RegisterCemeteryDeathRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            graveId: $grave->id,
            registrationNumber: '2026-REG-001',
            intermentDate: '2026-02-05',
            concessionYears: 20,
            registrationNotes: 'Standard 20-year concession',
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Response contains correct data
        $this->assertEquals($grave->id, $response->graveId);
        $this->assertEquals('A-101', $response->graveNumber);
        $this->assertEquals('2026-REG-001', $response->registrationNumber);
        $this->assertEquals('Mario Rossi', $response->deceasedName);

        // Assert: Grave updated correctly
        $grave->refresh();
        $this->assertEquals('occupied', $grave->status);
        $this->assertEquals($funeral->id, $grave->funeral_id);
        $this->assertEquals('Mario Rossi', $grave->occupant_name);
        $this->assertEquals('2026-REG-001', $grave->registration_number);

        // Assert: Concession expiration calculated correctly (20 years from interment)
        $expectedExpiration = Carbon::parse('2026-02-05')->addYears(20);
        $this->assertEquals(
            $expectedExpiration->format('Y-m-d'),
            Carbon::parse($grave->concession_expires_at)->format('Y-m-d')
        );
    }

    /** @test */
    public function it_calculates_perpetual_concession_correctly()
    {
        // Arrange
        $cemetery = Cemetery::factory()->create(['agency_id' => $this->tenantId]);
        $area = CemeteryArea::factory()->create(['cemetery_id' => $cemetery->id]);
        $grave = Grave::factory()->create([
            'cemetery_area_id' => $area->id,
            'status' => 'available',
        ]);

        $deceased = Deceased::factory()->create(['death_date' => Carbon::parse('2026-02-01')]);
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'funeral_id' => $deceased->id,
        ]);
        $deceased->update(['funeral_id' => $funeral->id]);

        $request = new RegisterCemeteryDeathRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            graveId: $grave->id,
            registrationNumber: '2026-REG-PERPETUAL',
            intermentDate: '2026-02-05',
            concessionYears: 99, // Perpetual
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Perpetual concession has null expiration
        $grave->refresh();
        $this->assertNull($grave->concession_expires_at);
        $this->assertEquals(99, $grave->concession_years);
        $this->assertStringContainsString('Perpetual', $response->concessionExpiresAt);
    }

    /** @test */
    public function it_prevents_registration_on_occupied_grave()
    {
        // Arrange: Grave already occupied
        $cemetery = Cemetery::factory()->create(['agency_id' => $this->tenantId]);
        $area = CemeteryArea::factory()->create(['cemetery_id' => $cemetery->id]);
        $grave = Grave::factory()->create([
            'cemetery_area_id' => $area->id,
            'status' => 'occupied', // Already occupied
            'occupant_name' => 'Existing Person',
        ]);

        $deceased = Deceased::factory()->create(['death_date' => Carbon::parse('2026-02-01')]);
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'funeral_id' => $deceased->id,
        ]);
        $deceased->update(['funeral_id' => $funeral->id]);

        $request = new RegisterCemeteryDeathRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            graveId: $grave->id,
            registrationNumber: '2026-REG-002',
            intermentDate: '2026-02-05',
            concessionYears: 20,
        );

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('currently occupied');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_prevents_interment_before_death_date()
    {
        // Arrange
        $cemetery = Cemetery::factory()->create(['agency_id' => $this->tenantId]);
        $area = CemeteryArea::factory()->create(['cemetery_id' => $cemetery->id]);
        $grave = Grave::factory()->create([
            'cemetery_area_id' => $area->id,
            'status' => 'available',
        ]);

        $deceased = Deceased::factory()->create([
            'death_date' => Carbon::parse('2026-02-10'), // Death on Feb 10
        ]);
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'funeral_id' => $deceased->id,
        ]);
        $deceased->update(['funeral_id' => $funeral->id]);

        $request = new RegisterCemeteryDeathRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            graveId: $grave->id,
            registrationNumber: '2026-REG-003',
            intermentDate: '2026-02-05', // Before death date!
            concessionYears: 20,
        );

        // Act & Assert: Domain rule violation
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('cannot be before the death date');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_prevents_duplicate_registration_numbers_in_same_cemetery()
    {
        // Arrange: Two graves in same cemetery
        $cemetery = Cemetery::factory()->create(['agency_id' => $this->tenantId]);
        $area = CemeteryArea::factory()->create(['cemetery_id' => $cemetery->id]);

        $existingGrave = Grave::factory()->create([
            'cemetery_area_id' => $area->id,
            'registration_number' => '2026-REG-DUPLICATE',
            'status' => 'occupied',
        ]);

        $newGrave = Grave::factory()->create([
            'cemetery_area_id' => $area->id,
            'status' => 'available',
        ]);

        $deceased = Deceased::factory()->create(['death_date' => Carbon::parse('2026-02-01')]);
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'funeral_id' => $deceased->id,
        ]);
        $deceased->update(['funeral_id' => $funeral->id]);

        $request = new RegisterCemeteryDeathRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            graveId: $newGrave->id,
            registrationNumber: '2026-REG-DUPLICATE', // Duplicate!
            intermentDate: '2026-02-05',
            concessionYears: 20,
        );

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('already used in the cemetery register');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_enforces_multi_tenant_isolation_for_funeral()
    {
        // Arrange: Funeral from different tenant
        $otherTenant = User::factory()->create(['agency_id' => 999]);

        $cemetery = Cemetery::factory()->create(['agency_id' => $this->tenantId]);
        $area = CemeteryArea::factory()->create(['cemetery_id' => $cemetery->id]);
        $grave = Grave::factory()->create([
            'cemetery_area_id' => $area->id,
            'status' => 'available',
        ]);

        $deceased = Deceased::factory()->create();
        $funeral = Funeral::factory()->create([
            'agency_id' => 999, // Different tenant!
            'funeral_id' => $deceased->id,
        ]);

        $request = new RegisterCemeteryDeathRequest(
            tenantId: $this->tenantId, // Request from tenant 1
            funeralId: $funeral->id,    // But funeral belongs to tenant 999
            graveId: $grave->id,
            registrationNumber: '2026-REG-CROSS-TENANT',
            intermentDate: '2026-02-05',
            concessionYears: 20,
        );

        // Act & Assert: Multi-tenant isolation prevents access
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_enforces_multi_tenant_isolation_for_grave()
    {
        // Arrange: Grave from different tenant's cemetery
        $otherCemetery = Cemetery::factory()->create(['agency_id' => 999]);
        $otherArea = CemeteryArea::factory()->create(['cemetery_id' => $otherCemetery->id]);
        $grave = Grave::factory()->create([
            'cemetery_area_id' => $otherArea->id,
            'status' => 'available',
        ]);

        $deceased = Deceased::factory()->create(['death_date' => Carbon::parse('2026-02-01')]);
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'funeral_id' => $deceased->id,
        ]);

        $request = new RegisterCemeteryDeathRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            graveId: $grave->id, // Grave from different tenant!
            registrationNumber: '2026-REG-004',
            intermentDate: '2026-02-05',
            concessionYears: 20,
        );

        // Act & Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_rejects_invalid_concession_years()
    {
        // Arrange
        $cemetery = Cemetery::factory()->create(['agency_id' => $this->tenantId]);
        $area = CemeteryArea::factory()->create(['cemetery_id' => $cemetery->id]);
        $grave = Grave::factory()->create([
            'cemetery_area_id' => $area->id,
            'status' => 'available',
        ]);

        $deceased = Deceased::factory()->create(['death_date' => Carbon::parse('2026-02-01')]);
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'funeral_id' => $deceased->id,
        ]);

        $request = new RegisterCemeteryDeathRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            graveId: $grave->id,
            registrationNumber: '2026-REG-005',
            intermentDate: '2026-02-05',
            concessionYears: 15, // Invalid! Only 10, 20, 30, 99 allowed
        );

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('must be one of: 10, 20, 30, or 99');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_allows_registration_on_reserved_grave()
    {
        // Arrange: Reserved grave (valid for registration)
        $cemetery = Cemetery::factory()->create(['agency_id' => $this->tenantId]);
        $area = CemeteryArea::factory()->create(['cemetery_id' => $cemetery->id]);
        $grave = Grave::factory()->create([
            'cemetery_area_id' => $area->id,
            'status' => 'reserved', // Reserved is OK
        ]);

        $deceased = Deceased::factory()->create(['death_date' => Carbon::parse('2026-02-01')]);
        $funeral = Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'funeral_id' => $deceased->id,
        ]);
        $deceased->update(['funeral_id' => $funeral->id]);

        $request = new RegisterCemeteryDeathRequest(
            tenantId: $this->tenantId,
            funeralId: $funeral->id,
            graveId: $grave->id,
            registrationNumber: '2026-REG-RESERVED',
            intermentDate: '2026-02-05',
            concessionYears: 30,
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Success
        $grave->refresh();
        $this->assertEquals('occupied', $grave->status);
        $this->assertNotNull($response->graveId);
    }
}
