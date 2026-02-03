<?php

namespace App\Application\UseCases\Funeral;

use App\Application\DTOs\Requests\Funeral\StoreFuneralRequest;
use App\Application\DTOs\Responses\Funeral\StoreFuneralResponse;
use App\Domain\Repositories\DeceasedRepositoryInterface;
use App\Domain\Repositories\FuneralRepositoryInterface;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

/**
 * Use Case: Store a new funeral from Timeline Wizard.
 *
 * This orchestrates the complete funeral creation process:
 * 1. Validate tax code uniqueness
 * 2. Create deceased record
 * 3. Create funeral record (triggers timeline auto-initialization via model event)
 * 4. Create initial quote with selected products
 *
 * All operations are wrapped in a database transaction.
 */
class StoreFuneralUseCase
{
    public function __construct(
        private FuneralRepositoryInterface $funeralRepository,
        private DeceasedRepositoryInterface $deceasedRepository,
    ) {
    }

    /**
     * Execute the use case.
     *
     * @throws \App\Domain\Exceptions\TimelineTemplateNotFoundException
     * @throws \App\Domain\Exceptions\ProductNotFoundException
     * @throws \App\Domain\Exceptions\InvalidFuneralDateException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function execute(StoreFuneralRequest $request): StoreFuneralResponse
    {
        // Business Rule 1: Timeline templates must exist for agency
        $templateCount = \App\Models\TimelineStep::where('agency_id', $request->tenantId)->count();
        if ($templateCount === 0) {
            throw new \App\Domain\Exceptions\TimelineTemplateNotFoundException($request->tenantId);
        }

        // Business Rule 2: Death date cannot be in the future
        if ($request->deceasedDeathDate && \Carbon\Carbon::parse($request->deceasedDeathDate)->isFuture()) {
            throw \App\Domain\Exceptions\InvalidFuneralDateException::deathInFuture();
        }

        // Business Rule 3: Ceremony date must be after death date
        if ($request->ceremonyDate && $request->deceasedDeathDate) {
            $ceremonyDate = \Carbon\Carbon::parse($request->ceremonyDate);
            $deathDate = \Carbon\Carbon::parse($request->deceasedDeathDate);

            if ($ceremonyDate->lt($deathDate)) {
                throw \App\Domain\Exceptions\InvalidFuneralDateException::ceremonyBeforeDeath();
            }
        }

        // Business Rule 4: Tax code must be unique for active funerals in tenant
        if (
            $this->funeralRepository->taxCodeExistsForActiveFuneral(
                $request->deceasedTaxCode,
                $request->tenantId
            )
        ) {
            throw new \Exception('The deceased tax code has already been taken for an active funeral.');
        }

        // Business Rule 5: All selected products must exist and belong to tenant
        if (!empty($request->productIds)) {
            $validProducts = Product::where('agency_id', $request->tenantId)
                ->whereIn('id', $request->productIds)
                ->whereNull('deleted_at') // CRITICAL: Exclude soft-deleted products
                ->pluck('id')
                ->toArray();

            $missingProductIds = array_diff($request->productIds, $validProducts);

            if (count($missingProductIds) > 0) {
                throw new \App\Domain\Exceptions\ProductNotFoundException(
                    $missingProductIds[0],
                    $request->tenantId
                );
            }
        }

        return DB::transaction(function () use ($request) {
            // CRITICAL FIX: Concurrency Safety
            // Lock the Agency record to serialize funeral creation for this tenant.
            // This prevents race conditions in funeral_code generation (e.g. FUN-2024-001 duplication).
            \App\Models\Agency::where('id', $request->tenantId)->lockForUpdate()->first();

            // Step 1: Create deceased record
            $deceased = $this->deceasedRepository->create([
                'first_name' => $request->deceasedName,
                'last_name' => $request->deceasedSurname,
                'tax_code' => $request->deceasedTaxCode,
                'birth_date' => $request->deceasedBirthDate,
                'place_of_birth' => $request->deceasedBirthCity,
                'death_date' => $request->deceasedDeathDate,
                'place_of_death' => $request->deceasedDeathCity,
            ], $request->tenantId);

            // Step 2: Create funeral (timeline initialization happens in model event)
            $funeral = $this->funeralRepository->create([
                'service_type' => $request->ceremonyType,
                'ceremony_location' => $request->ceremonyLocation,
                'ceremony_date' => $request->ceremonyDate,
                'notes' => $request->notes,
                'funeral_id' => $deceased->id,  // Link to deceased
                'status' => 'draft',
            ], $request->tenantId);

            // Link deceased to funeral (bidirectional relationship)
            $deceased->update(['funeral_id' => $funeral->id]);

            // Step 3: Create initial quote with selected products
            if (!empty($request->productIds)) {
                $quote = Quote::create([
                    'funeral_id' => $funeral->id,
                    'agency_id' => $request->tenantId,
                    'status' => 'draft',
                    'valid_until' => now()->addDays(30),
                ]);

                // CRITICAL FIX: Product Ownership & Deletion Validation
                // Ensure we only attach products that belong to the agency AND are not deleted.
                $products = Product::where('agency_id', $request->tenantId)
                    ->whereIn('id', $request->productIds)
                    ->whereNull('deleted_at') // Added explicit deletion check
                    ->get();

                foreach ($products as $product) {
                    $quote->items()->create([
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit_price' => $product->price,
                        'total_price' => $product->price,
                        'description' => $product->name,
                    ]);
                }
            }

            // Step 4: Store required documents metadata
            // (actual document generation happens separately)
            foreach ($request->requiredDocuments as $documentType) {
                $funeral->documents()->create([
                    'document_type' => $documentType,
                    'status' => 'pending',
                    'agency_id' => $request->tenantId,
                ]);
            }

            // Reload relationships for response
            $funeral->load(['timeline', 'activeQuote.items', 'deceased']);

            return StoreFuneralResponse::fromFuneral($funeral);
        });
    }
}
