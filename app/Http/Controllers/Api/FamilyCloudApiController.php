<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Family\AuthenticateFamilyMemberUseCase;
use App\Application\UseCases\Family\GetFamilyDashboardDataUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FamilyCloudApiController extends Controller
{
    /**
     * Login family member.
     * 
     * POST /api/family/login
     */
    public function login(Request $request, AuthenticateFamilyMemberUseCase $useCase): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $token = $useCase->execute($request->token);

            // In a real SPA, we'd return a Sanctum token. 
            // Here we return success and the frontend stores the token in localStorage/session
            return response()->json([
                'message' => 'Login effettuato con successo',
                'token' => $token, // Return purely for confirmation
                'redirect_url' => route('family.dashboard')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    /**
     * Get dashboard data.
     * 
     * GET /api/family/dashboard
     */
    public function dashboard(Request $request, GetFamilyDashboardDataUseCase $useCase): JsonResponse
    {
        // Token is passed via header "X-Family-Token" or query param
        $token = $request->header('X-Family-Token') ?? $request->query('token');

        if (!$token) {
            return response()->json(['message' => 'Token mancante'], 401);
        }

        try {
            $data = $useCase->execute($token);
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error("Family Dashboard Error: " . $e->getMessage());
            return response()->json(['message' => 'Accesso negato o dati non trovati'], 403);
        }
    }
}
