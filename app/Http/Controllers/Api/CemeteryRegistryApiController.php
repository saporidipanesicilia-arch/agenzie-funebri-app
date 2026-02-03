<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Cemetery\GetCemeteryMapUseCase;
use App\Application\UseCases\Cemetery\SearchCeteriesUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CemeteryRegistryApiController extends Controller
{
    /**
     * Search cemeteries.
     * 
     * GET /api/cemeteries
     */
    public function search(Request $request, SearchCeteriesUseCase $useCase): JsonResponse
    {
        $query = $request->query('q');

        $cemeteries = $useCase->execute($query);

        return response()->json([
            'data' => $cemeteries
        ]);
    }

    /**
     * Get cemetery map/details.
     * 
     * GET /api/cemeteries/{id}/map
     */
    public function map(int $id, GetCemeteryMapUseCase $useCase): JsonResponse
    {
        try {
            $data = $useCase->execute($id);
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Cimitero non trovato'], 404);
        }
    }
}
