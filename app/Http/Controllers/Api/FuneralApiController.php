<?php

namespace App\Http\Controllers\Api;

use App\Application\DTOs\Requests\Funeral\SaveFuneralDraftRequest;
use App\Application\DTOs\Requests\Funeral\StoreFuneralRequest;
use App\Application\UseCases\Funeral\RetrieveFuneralDraftUseCase;
use App\Application\UseCases\Funeral\SaveFuneralDraftUseCase;
use App\Application\UseCases\Funeral\StoreFuneralUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Funeral\SaveFuneralDraftHttpRequest;
use App\Http\Requests\Api\Funeral\StoreFuneralHttpRequest;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for Funeral operations.
 * 
 * This controller is THIN - it only orchestrates between HTTP layer and Use Cases.
 * No business logic here.
 */
class FuneralApiController extends Controller
{
    /**
     * Store a new funeral from Timeline Wizard.
     * 
     * POST /api/funerals
     */
    public function store(
        StoreFuneralHttpRequest $httpRequest,
        StoreFuneralUseCase $useCase
    ): JsonResponse {
        $request = StoreFuneralRequest::fromArray(
            tenantId: auth()->user()->agency_id,
            data: $httpRequest->validated()
        );

        try {
            $response = $useCase->execute($request);

            return response()->json($response->toArray(), 201);
        } catch (\App\Domain\Exceptions\TimelineTemplateNotFoundException $e) {
            return response()->json([
                'message' => 'Configurazione timeline non trovata per questa agenzia. Contatta il supporto.',
                'error_code' => 'TIMELINE_CONFIG_MISSING',
            ], 422);
        } catch (\App\Domain\Exceptions\InvalidFuneralDateException $e) {
            return response()->json([
                'message' => $e->getMessage(), // Domain exception messages are safe for users
                'error_code' => 'INVALID_DATE',
            ], 422);
        } catch (\App\Domain\Exceptions\ProductNotFoundException $e) {
            return response()->json([
                'message' => 'Uno o più prodotti selezionati non sono disponibili o validi.',
                'error_code' => 'PRODUCT_INVALID',
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions to be handled by Laravel's default handler
            throw $e;
        } catch (\Exception $e) {
            // CRITICAL: Log detailed error server-side, return generic message to client
            \Log::error('Funeral creation failed', [
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->agency_id ?? 'unknown',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Si è verificato un errore imprevisto durante la creazione della pratica. Riprova più tardi.',
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'status_code' => 500,
            ], 500);
        }
    }

    /**
     * Save wizard draft.
     * 
     * POST /api/funerals/drafts
     */
    public function saveDraft(
        SaveFuneralDraftHttpRequest $httpRequest,
        SaveFuneralDraftUseCase $useCase
    ): JsonResponse {
        try {
            $request = SaveFuneralDraftRequest::fromArray(
                tenantId: auth()->user()->agency_id,
                data: $httpRequest->validated()
            );

            $response = $useCase->execute($request);

            return response()->json($response->toArray(), $httpRequest->filled('draft_id') ? 200 : 201);
        } catch (\App\Domain\Exceptions\InvalidFuneralDateException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => 'INVALID_DATE',
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Draft save failed', [
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->agency_id ?? 'unknown',
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Impossibile salvare la bozza. Riprova più tardi.',
                'error_code' => 'DRAFT_SAVE_FAILED',
                'status_code' => 500,
            ], 500);
        }
    }

    /**
     * Retrieve wizard draft.
     * 
     * GET /api/funerals/drafts/{draft}
     */
    public function getDraft(
        int $draftId,
        RetrieveFuneralDraftUseCase $useCase
    ): JsonResponse {
        try {
            $response = $useCase->execute(
                draftId: $draftId,
                tenantId: auth()->user()->agency_id
            );

            return response()->json($response->toArray());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Bozza non trovata o non accessibile.',
                'error_code' => 'DRAFT_NOT_FOUND',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Draft retrieval failed', [
                'user_id' => auth()->id(),
                'draft_id' => $draftId,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Errore nel recupero della bozza.',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }
}
