// Generate Family Access Token Edge Function
// Creates secure token for family cloud access

import { getUserContext, hasRole } from '../_shared/auth.ts'
import { getSupabaseClient } from '../_shared/db.ts'
import { handleError, successResponse, SafeError } from '../_shared/errors.ts'

interface RequestBody {
    funeral_id: string
    expires_in_days?: number
}

/**
 * Generate cryptographically secure random token
 */
function generateSecureToken(length: number = 32): string {
    const array = new Uint8Array(length)
    crypto.getRandomValues(array)
    return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('')
}

Deno.serve(async (req) => {
    try {
        if (req.method !== 'POST') {
            return new Response('Method Not Allowed', { status: 405 })
        }

        const userContext = await getUserContext(req)
        const { agencyId, userId, role, token } = userContext

        if (!hasRole(role, 'operator')) {
            throw new SafeError(
                'INSUFFICIENT_PERMISSIONS',
                'Solo gli operatori possono generare token di accesso.',
                403
            )
        }

        const body: RequestBody = await req.json()

        if (!body.funeral_id) {
            throw new SafeError('VALIDATION_ERROR', 'ID funerale obbligatorio.')
        }

        const expiresInDays = body.expires_in_days || 30
        if (expiresInDays < 1 || expiresInDays > 365) {
            throw new SafeError('VALIDATION_ERROR', 'La durata deve essere tra 1 e 365 giorni.')
        }

        const supabase = getSupabaseClient(token)

        // Verify funeral belongs to agency
        const { data: funeral, error: funeralError } = await supabase
            .from('funerals')
            .select('id, agency_id, funeral_code, deceased:deceased(first_name, last_name)')
            .eq('id', body.funeral_id)
            .single()

        if (funeralError || !funeral) {
            throw new Error('NOT_FOUND')
        }

        if (funeral.agency_id !== agencyId) {
            throw new Error('FORBIDDEN')
        }

        // Generate secure token
        const accessToken = generateSecureToken(32)

        // Calculate expiration
        const expiresAt = new Date()
        expiresAt.setDate(expiresAt.getDate() + expiresInDays)

        // Insert token
        const { data: familyToken, error: tokenError } = await supabase
            .from('family_access_tokens')
            .insert({
                funeral_id: body.funeral_id,
                token: accessToken,
                expires_at: expiresAt.toISOString(),
                created_by_user_id: userId,
                access_count: 0
            })
            .select()
            .single()

        if (tokenError) {
            console.error('Failed to create family token:', tokenError)
            throw tokenError
        }

        // Generate public URL
        const baseUrl = Deno.env.get('PUBLIC_SITE_URL') || 'https://yourapp.com'
        const publicUrl = `${baseUrl}/family-cloud?token=${accessToken}`

        return successResponse({
            token: accessToken,
            public_url: publicUrl,
            expires_at: familyToken.expires_at,
            funeral: {
                id: funeral.id,
                code: funeral.funeral_code,
                deceased_name: `${funeral.deceased.first_name} ${funeral.deceased.last_name}`
            }
        }, 201)

    } catch (error) {
        return handleError(error)
    }
})
