<?php

namespace Tests\Integration\UseCases\Funeral;

use App\Application\DTOs\Requests\Funeral\StoreFuneralRequest;
use App\Application\UseCases\Funeral\StoreFuneralUseCase;
use App\Domain\Exceptions\InvalidFuneralDateException;
use App\Domain\Exceptions\ProductNotFoundException;
use App\Domain\Exceptions\TimelineTemplateNotFoundException;
use App\Models\Deceased;
use App\Models\Funeral;
use App\Models\Product;
use App\Models\TimelineStep;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration Tests: StoreFuneralUseCase (WITH VALIDATIONS)
 *
 * This is the PRIMARY use case of the system - complete funeral creation from wizard.
 *
 * Critical scenarios:
 * - Complete wizard flow (deceased + ceremony + products + documents)
 * - Tax code uniqueness validation
 * - Timeline auto-initialization
 * - Quote creation with selected products
 * - Multi-tenant and multi-branch isolation
 * - Required vs optional data
 * - Transaction integrity and rollback
 * - VALIDATION RULES (NEW):
 *   - Timeline templates must exist
 *   - Products must exist and belong to tenant
 *   - Death date ≤ today
 *   - Ceremony date ≥ death date
 */
class StoreFuneralUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private StoreFuneralUseCase $useCase;
    private User $tenantUser;
    private int $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(StoreFuneralUseCase::class);

        // Create tenant with timeline templates
        $this->tenantUser = User::factory()->create([
            'agency_id' => 1,
            'role' => 'operator',
        ]);
        $this->tenantId = $this->tenantUser->agency_id;

        // Seed timeline steps for this tenant
        TimelineStep::factory()->count(5)->create([
            'agency_id' => $this->tenantId,
        ]);
    }

    /** @test */
    public function it_creates_funeral_with_complete_wizard_data()
    {
        // Arrange: Complete wizard submission
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Mario',
            deceasedSurname: 'Rossi',
            deceasedTaxCode: 'RSSMRA45L01F205Z',
            deceasedBirthDate: '1945-07-01',
            deceasedBirthCity: 'Milano',
            deceasedDeathDate: '2026-02-01 14:30',
            deceasedDeathCity: 'Milano',
            ceremonyType: 'burial',
            ceremonyLocation: 'Chiesa San Marco',
            ceremonyDate: '2026-02-05 10:00',
            productIds: [],
            requiredDocuments: ['certificate_death', 'burial_permit'],
            notes: 'Famiglia richiede cerimonia sobria',
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Response structure
        $this->assertNotNull($response->funeralId);
        $this->assertMatchesRegularExpression('/^FUN-\d{4}-\d{3}$/', $response->funeralCode);
        $this->assertEquals('draft', $response->status);
        $this->assertGreaterThan(0, count($response->timelineSteps));

        // Assert: Funeral created in database
        $funeral = Funeral::find($response->funeralId);
        $this->assertNotNull($funeral);
        $this->assertEquals($this->tenantId, $funeral->agency_id);
        $this->assertEquals('burial', $funeral->service_type);
        $this->assertEquals('Chiesa San Marco', $funeral->ceremony_location);
        $this->assertEquals('draft', $funeral->status);
        $this->assertStringContainsString('Famiglia richiede cerimonia sobria', $funeral->notes);

        // Assert: Deceased created and linked
        $this->assertNotNull($funeral->deceased);
        $this->assertEquals('Mario', $funeral->deceased->first_name);
        $this->assertEquals('Rossi', $funeral->deceased->last_name);
        $this->assertEquals('RSSMRA45L01F205Z', $funeral->deceased->tax_code);
        $this->assertEquals('1945-07-01', $funeral->deceased->birth_date->format('Y-m-d'));
        $this->assertEquals('Milano', $funeral->deceased->place_of_birth);
    }

    /** @test */
    public function it_throws_exception_when_no_timeline_templates_exist()
    {
        // Arrange: Delete all timeline templates
        TimelineStep::where('agency_id', $this->tenantId)->delete();

        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Test',
            deceasedSurname: 'User',
            deceasedTaxCode: 'TSTUSER70A01F205A',
            deceasedBirthDate: '1970-01-01',
            deceasedBirthCity: 'Test',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'Test',
            ceremonyType: 'burial',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [],
            requiredDocuments: [],
        );

        // Act & Assert
        $this->expectException(TimelineTemplateNotFoundException::class);
        $this->expectExceptionMessage('No timeline templates found');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_throws_exception_when_death_date_is_in_future()
    {
        // Arrange: Death date tomorrow
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Future',
            deceasedSurname: 'Death',
            deceasedTaxCode: 'FUTDTH70A01F205B',
            deceasedBirthDate: '1970-01-01',
            deceasedBirthCity: 'Test',
            deceasedDeathDate: Carbon::tomorrow()->format('Y-m-d'),
            deceasedDeathCity: 'Test',
            ceremonyType: 'burial',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [],
            requiredDocuments: [],
        );

        // Act & Assert
        $this->expectException(InvalidFuneralDateException::class);
        $this->expectExceptionMessage('Death date cannot be in the future');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_throws_exception_when_ceremony_before_death()
    {
        // Arrange: Ceremony before death (impossible)
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Invalid',
            deceasedSurname: 'Ceremony',
            deceasedTaxCode: 'INVCER70A01F205C',
            deceasedBirthDate: '1970-01-01',
            deceasedBirthCity: 'Test',
            deceasedDeathDate: '2026-02-10',
            deceasedDeathCity: 'Test',
            ceremonyType: 'burial',
            ceremonyLocation: 'Church',
            ceremonyDate: '2026-02-05', // Before death!
            productIds: [],
            requiredDocuments: [],
        );

        // Act & Assert
        $this->expectException(InvalidFuneralDateException::class);
        $this->expectExceptionMessage('Ceremony date cannot be before the death date');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_throws_exception_when_product_does_not_exist()
    {
        // Arrange: Non-existent product ID
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Invalid',
            deceasedSurname: 'Product',
            deceasedTaxCode: 'INVPRD70A01F205D',
            deceasedBirthDate: '1970-01-01',
            deceasedBirthCity: 'Test',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'Test',
            ceremonyType: 'burial',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [99999], // Invalid!
            requiredDocuments: [],
        );

        // Act & Assert
        $this->expectException(ProductNotFoundException::class);
        $this->expectExceptionMessage('not found or does not belong');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_throws_exception_when_product_belongs_to_different_tenant()
    {
        // Arrange: Product from different tenant
        $foreignProduct = Product::factory()->create([
            'agency_id' => 999, // Different tenant
        ]);

        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Foreign',
            deceasedSurname: 'Product',
            deceasedTaxCode: 'FRGPRD70A01F205E',
            deceasedBirthDate: '1970-01-01',
            deceasedBirthCity: 'Test',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'Test',
            ceremonyType: 'burial',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [$foreignProduct->id],
            requiredDocuments: [],
        );

        // Act & Assert
        $this->expectException(ProductNotFoundException::class);

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_initializes_timeline_automatically()
    {
        // Arrange
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Luigi',
            deceasedSurname: 'Verdi',
            deceasedTaxCode: 'VRDLGU50M01F205X',
            deceasedBirthDate: '1950-08-01',
            deceasedBirthCity: 'Roma',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'Roma',
            ceremonyType: 'cremation',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [],
            requiredDocuments: [],
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Timeline initialized
        $this->assertCount(5, $response->timelineSteps); // 5 template steps created earlier
        $this->assertArrayHasKey('id', $response->timelineSteps[0]);
        $this->assertArrayHasKey('step_name', $response->timelineSteps[0]);
        $this->assertArrayHasKey('status', $response->timelineSteps[0]);

        // Assert: All steps are pending
        foreach ($response->timelineSteps as $step) {
            $this->assertEquals('pending', $step['status']);
        }

        // Assert: Database verification
        $funeral = Funeral::find($response->funeralId);
        $this->assertCount(5, $funeral->timeline);
    }

    /** @test */
    public function it_creates_quote_with_selected_products()
    {
        // Arrange: Products available
        $coffin = Product::factory()->create([
            'agency_id' => $this->tenantId,
            'name' => 'Bare Rovere',
            'type' => 'coffin',
            'price' => 2500.00,
        ]);

        $urn = Product::factory()->create([
            'agency_id' => $this->tenantId,
            'name' => 'Urna Ceramica',
            'type' => 'urn',
            'price' => 450.00,
        ]);

        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Anna',
            deceasedSurname: 'Bianchi',
            deceasedTaxCode: 'BNCNNA60D41F205Y',
            deceasedBirthDate: '1960-04-01',
            deceasedBirthCity: 'Torino',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'Torino',
            ceremonyType: 'burial',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [$coffin->id, $urn->id],
            requiredDocuments: [],
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Quote created
        $funeral = Funeral::with('activeQuote.items')->find($response->funeralId);
        $this->assertNotNull($funeral->activeQuote);
        $this->assertCount(2, $funeral->activeQuote->items);

        // Assert: Quote items have correct products and prices
        $items = $funeral->activeQuote->items;
        $this->assertEquals('Bare Rovere', $items[0]->description);
        $this->assertEquals(2500.00, $items[0]->unit_price);
        $this->assertEquals('Urna Ceramica', $items[1]->description);
        $this->assertEquals(450.00, $items[1]->unit_price);

        // Assert: Estimated total calculated
        $this->assertEquals(2950.00, $response->estimatedTotal);
    }

    /** @test */
    public function it_prevents_duplicate_tax_code_for_active_funerals()
    {
        // Arrange: Existing funeral with tax code
        $existingDeceased = Deceased::factory()->create([
            'tax_code' => 'RSSMRA45L01F205Z',
        ]);
        Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'funeral_id' => $existingDeceased->id,
            'status' => 'active', // Active funeral
        ]);
        $existingDeceased->update(['funeral_id' => Funeral::first()->id]);

        // Attempt to create new funeral with same tax code
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Mario',
            deceasedSurname: 'Rossi',
            deceasedTaxCode: 'RSSMRA45L01F205Z', // Duplicate!
            deceasedBirthDate: '1945-07-01',
            deceasedBirthCity: 'Milano',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'Milano',
            ceremonyType: 'burial',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [],
            requiredDocuments: [],
        );

        // Act & Assert: Business rule violation
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('tax code has already been taken');

        $this->useCase->execute($request);
    }

    /** @test */
    public function it_allows_duplicate_tax_code_for_closed_funerals()
    {
        // Arrange: Existing CLOSED funeral with tax code
        $existingDeceased = Deceased::factory()->create([
            'tax_code' => 'RSSMRA45L01F205Z',
        ]);
        Funeral::factory()->create([
            'agency_id' => $this->tenantId,
            'funeral_id' => $existingDeceased->id,
            'status' => 'closed', // Closed, not active
        ]);
        $existingDeceased->update(['funeral_id' => Funeral::first()->id]);

        // New funeral with same tax code should succeed
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Mario',
            deceasedSurname: 'Rossi',
            deceasedTaxCode: 'RSSMRA45L01F205Z',
            deceasedBirthDate: '1945-07-01',
            deceasedBirthCity: 'Milano',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'Milano',
            ceremonyType: 'burial',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [],
            requiredDocuments: [],
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Success (closed funerals don't block)
        $this->assertNotNull($response->funeralId);
        $this->assertNotEquals(Funeral::first()->id, $response->funeralId);
    }

    /** @test */
    public function it_generates_unique_funeral_codes_per_tenant()
    {
        // Arrange: Create multiple funerals in same tenant
        $requests = [];
        for ($i = 1; $i <= 3; $i++) {
            $requests[] = new StoreFuneralRequest(
                tenantId: $this->tenantId,
                deceasedName: "Test{$i}",
                deceasedSurname: 'User',
                deceasedTaxCode: "TSTU0{$i}70A01F205X",
                deceasedBirthDate: '1970-01-01',
                deceasedBirthCity: 'Test',
                deceasedDeathDate: '2026-02-01',
                deceasedDeathCity: 'Test',
                ceremonyType: 'burial',
                ceremonyLocation: null,
                ceremonyDate: null,
                productIds: [],
                requiredDocuments: [],
            );
        }

        // Act: Create 3 funerals
        $funeralCodes = [];
        foreach ($requests as $request) {
            $response = $this->useCase->execute($request);
            $funeralCodes[] = $response->funeralCode;
        }

        // Assert: All codes unique
        $this->assertCount(3, array_unique($funeralCodes));

        // Assert: Format FUN-2026-001, FUN-2026-002, FUN-2026-003
        $this->assertEquals('FUN-2026-001', $funeralCodes[0]);
        $this->assertEquals('FUN-2026-002', $funeralCodes[1]);
        $this->assertEquals('FUN-2026-003', $funeralCodes[2]);
    }

    /** @test */
    public function it_handles_optional_fields_correctly()
    {
        // Arrange: Minimal required data only
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Minimal',
            deceasedSurname: 'Test',
            deceasedTaxCode: 'MNMTST70A01F205B',
            deceasedBirthDate: '1970-01-01',
            deceasedBirthCity: 'City',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'City',
            ceremonyType: 'burial',
            ceremonyLocation: null, // Optional
            ceremonyDate: null,     // Optional
            productIds: [],         // Optional
            requiredDocuments: [],  // Optional
            notes: null,            // Optional
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Success with minimal data
        $this->assertNotNull($response->funeralId);

        $funeral = Funeral::find($response->funeralId);
        $this->assertNull($funeral->ceremony_location);
        $this->assertNull($funeral->ceremony_date);
        $this->assertNull($funeral->notes);
        $this->assertEquals(0.0, $response->estimatedTotal);
    }

    /** @test */
    public function it_creates_document_records_for_required_documents()
    {
        // Arrange
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Document',
            deceasedSurname: 'Test',
            deceasedTaxCode: 'DCMTST70A01F205C',
            deceasedBirthDate: '1970-01-01',
            deceasedBirthCity: 'City',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'City',
            ceremonyType: 'cremation',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [],
            requiredDocuments: ['certificate_death', 'cremation_request', 'identity_document'],
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Document records created
        $funeral = Funeral::with('documents')->find($response->funeralId);
        $this->assertCount(3, $funeral->documents);

        $documentTypes = $funeral->documents->pluck('document_type')->toArray();
        $this->assertContains('certificate_death', $documentTypes);
        $this->assertContains('cremation_request', $documentTypes);
        $this->assertContains('identity_document', $documentTypes);

        // Assert: All documents pending
        foreach ($funeral->documents as $doc) {
            $this->assertEquals('pending', $doc->status);
        }
    }

    /** @test */
    public function it_uses_draft_status_by_default()
    {
        // Arrange
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Status',
            deceasedSurname: 'Test',
            deceasedTaxCode: 'STTTST70A01F205D',
            deceasedBirthDate: '1970-01-01',
            deceasedBirthCity: 'City',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'City',
            ceremonyType: 'burial',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [],
            requiredDocuments: [],
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Status is draft
        $this->assertEquals('draft', $response->status);

        $funeral = Funeral::find($response->funeralId);
        $this->assertEquals('draft', $funeral->status);
    }

    /** @test */
    public function it_rolls_back_on_validation_failure()
    {
        // Arrange: Invalid product will trigger rollback
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Rollback',
            deceasedSurname: 'Test',
            deceasedTaxCode: 'RLLBCK70A01F205E',
            deceasedBirthDate: '1970-01-01',
            deceasedBirthCity: 'City',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'City',
            ceremonyType: 'burial',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [99999], // Invalid product ID
            requiredDocuments: [],
        );

        // Count records before
        $funeralsBefore = Funeral::count();
        $deceasedBefore = Deceased::count();

        // Act: Expect failure
        try {
            $this->useCase->execute($request);
            $this->fail('Exception should have been thrown');
        } catch (ProductNotFoundException $e) {
            // Expected
        }

        // Assert: NO records created (validation fails BEFORE transaction)
        $this->assertEquals($funeralsBefore, Funeral::count());
        $this->assertEquals($deceasedBefore, Deceased::count());
    }

    /** @test */
    public function it_links_deceased_and_funeral_bidirectionally()
    {
        // Arrange
        $request = new StoreFuneralRequest(
            tenantId: $this->tenantId,
            deceasedName: 'Bidirectional',
            deceasedSurname: 'Link',
            deceasedTaxCode: 'BDRLNK70A01F205F',
            deceasedBirthDate: '1970-01-01',
            deceasedBirthCity: 'City',
            deceasedDeathDate: '2026-02-01',
            deceasedDeathCity: 'City',
            ceremonyType: 'burial',
            ceremonyLocation: null,
            ceremonyDate: null,
            productIds: [],
            requiredDocuments: [],
        );

        // Act
        $response = $this->useCase->execute($request);

        // Assert: Bidirectional link
        $funeral = Funeral::find($response->funeralId);
        $deceased = $funeral->deceased;

        $this->assertEquals($deceased->id, $funeral->funeral_id);
        $this->assertEquals($funeral->id, $deceased->funeral_id);
    }
}
