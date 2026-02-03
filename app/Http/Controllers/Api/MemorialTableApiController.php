<?php

namespace App\Http\Controllers\Api;

use App\Application\DTOs\Requests\Product\ListProductsRequest;
use App\Application\UseCases\Product\ListProductsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemorialTableApiController extends Controller
{
    /**
     * List all products for the memorial table.
     * 
     * GET /api/products
     */
    public function listProducts(Request $request, ListProductsUseCase $useCase): JsonResponse
    {
        $dto = new ListProductsRequest(
            tenantId: auth()->user()->agency_id,
            category: $request->query('category')
        );

        $products = $useCase->execute($dto);

        return response()->json([
            'data' => $products
        ]);
    }
}
