<?php

namespace App\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTenantIsSet
 * 
 * Middleware che verifica che l'utente autenticato abbia un agency_id valido.
 * Previene accessi senza tenant configurato.
 */
class EnsureTenantIsSet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se l'utente è autenticato, verifica che abbia un agency_id
        if (auth()->check()) {
            $user = auth()->user();

            if (!$user->agency_id) {
                // Logout forzato e redirect al login
                auth()->logout();
                return redirect()->route('login')
                    ->withErrors(['error' => 'Account non associato a nessuna agenzia.']);
            }

            // Verifica che l'agenzia sia attiva
            if ($user->agency && !$user->agency->is_active) {
                auth()->logout();
                return redirect()->route('login')
                    ->withErrors(['error' => 'Account sospeso. Contattare l\'amministratore.']);
            }

            // Verifica che l'utente sia attivo
            if (!$user->is_active) {
                auth()->logout();
                return redirect()->route('login')
                    ->withErrors(['error' => 'Account disattivato.']);
            }

            // Salva il tenant corrente nella sessione (opzionale, per debug)
            session(['current_agency_id' => $user->agency_id]);
            session(['current_branch_id' => $user->branch_id]);
        }

        return $next($request);
    }
}
