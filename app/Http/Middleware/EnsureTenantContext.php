<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTenantContext Middleware
 * 
 * CRITICAL SECURITY: Multi-tenant isolation enforcement at HTTP boundary.
 * 
 * This middleware ensures:
 * 1. User is authenticated
 * 2. User has a valid agency_id (tenant context)
 * 3. Tenant context is set BEFORE any controller logic
 * 4. No request can bypass tenant isolation
 * 
 * Defense in depth: Works alongside TenantScope global query scope.
 */
class EnsureTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // RULE 1: User must be authenticated
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Non autenticato. Effettua il login.',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // RULE 2: User must have agency_id (tenant context)
        $user = auth()->user();

        if (!$user->agency_id || !is_numeric($user->agency_id)) {
            \Log::critical('User without tenant context attempted access', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'route' => $request->path(),
            ]);

            return response()->json([
                'message' => 'Contesto tenant non disponibile. Contatta il supporto.',
                'error_code' => 'NO_TENANT_CONTEXT',
            ], 403);
        }

        // RULE 3: Prevent agency_id manipulation
        // If request contains agency_id in body/query, validate it matches auth context
        $requestAgencyId = $request->input('agency_id') ?? $request->query('agency_id');

        if ($requestAgencyId && (int) $requestAgencyId !== (int) $user->agency_id) {
            \Log::warning('Cross-tenant access attempt detected', [
                'user_id' => $user->id,
                'user_agency_id' => $user->agency_id,
                'requested_agency_id' => $requestAgencyId,
                'ip' => $request->ip(),
                'route' => $request->path(),
            ]);

            return response()->json([
                'message' => 'Accesso negato: tentativo di accesso cross-tenant rilevato.',
                'error_code' => 'CROSS_TENANT_ACCESS_DENIED',
            ], 403);
        }

        // RULE 4: Store tenant context in request for logging/debugging
        // This allows controllers to access tenant context without auth()->user()->agency_id
        $request->attributes->set('tenant_id', $user->agency_id);

        // All checks passed - proceed to controller
        return $next($request);
    }
}
