// Safe error handling with localized messages
// Never exposes stack traces to clients

export class SafeError extends Error {
    constructor(
        public code: string,
        public message: string,
        public statusCode: number = 400
    ) {
        super(message)
        this.name = 'SafeError'
    }
}

/**
 * Error code to localized message mapping (Italian)
 */
const ERROR_MESSAGES: Record<string, { message: string; status: number }> = {
    'AUTH_REQUIRED': {
        message: 'Autenticazione richiesta. Effettua il login.',
        status: 401
    },
    'USER_PROFILE_NOT_FOUND': {
        message: 'Profilo utente non trovato o non attivo.',
        status: 403
    },
    'FORBIDDEN': {
        message: 'Accesso negato. Non hai i permessi per questa risorsa.',
        status: 403
    },
    'NOT_FOUND': {
        message: 'Risorsa non trovata.',
        status: 404
    },
    'VALIDATION_ERROR': {
        message: 'Dati non validi. Controlla i campi inseriti.',
        status: 400
    },
    'CONFLICT': {
        message: 'Conflitto con un\'operazione esistente.',
        status: 409
    },
    'INSUFFICIENT_PERMISSIONS': {
        message: 'Permessi insufficienti per questa operazione.',
        status: 403
    },
    'RESOURCE_UNAVAILABLE': {
        message: 'Risorsa non disponibile.',
        status: 409
    },
    'INTERNAL_ERROR': {
        message: 'Errore interno del server. Riprova più tardi.',
        status: 500
    }
}

/**
 * Convert any error to a safe Response
 * Logs detailed error server-side, returns localized message to client
 */
export function handleError(error: unknown): Response {
    console.error('Edge Function Error:', error)

    // Handle SafeError
    if (error instanceof SafeError) {
        return new Response(
            JSON.stringify({
                error: true,
                code: error.code,
                message: error.message
            }),
            {
                status: error.statusCode,
                headers: { 'Content-Type': 'application/json' }
            }
        )
    }

    // Handle known error codes
    if (error instanceof Error && error.message in ERROR_MESSAGES) {
        const { message, status } = ERROR_MESSAGES[error.message]
        return new Response(
            JSON.stringify({
                error: true,
                code: error.message,
                message
            }),
            {
                status,
                headers: { 'Content-Type': 'application/json' }
            }
        )
    }

    // Handle Supabase errors
    if (typeof error === 'object' && error !== null && 'code' in error) {
        const pgError = error as { code: string; message: string }

        // Map common PostgreSQL errors
        if (pgError.code === '23505') {
            return new Response(
                JSON.stringify({
                    error: true,
                    code: 'CONFLICT',
                    message: 'Un record con questi dati esiste già.'
                }),
                {
                    status: 409,
                    headers: { 'Content-Type': 'application/json' }
                }
            )
        }

        if (pgError.code === '23503') {
            return new Response(
                JSON.stringify({
                    error: true,
                    code: 'VALIDATION_ERROR',
                    message: 'Riferimento a dati non esistenti.'
                }),
                {
                    status: 400,
                    headers: { 'Content-Type': 'application/json' }
                }
            )
        }
    }

    // Generic error
    return new Response(
        JSON.stringify({
            error: true,
            code: 'INTERNAL_ERROR',
            message: ERROR_MESSAGES['INTERNAL_ERROR'].message
        }),
        {
            status: 500,
            headers: { 'Content-Type': 'application/json' }
        }
    )
}

/**
 * Create a success response
 */
export function successResponse(data: any, status: number = 200): Response {
    return new Response(
        JSON.stringify(data),
        {
            status,
            headers: { 'Content-Type': 'application/json' }
        }
    )
}
