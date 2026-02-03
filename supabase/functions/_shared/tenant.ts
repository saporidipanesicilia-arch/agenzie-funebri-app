// Tenant isolation utilities
// Ensures users can only access data from their agency

import { createClient } from 'https://esm.sh/@supabase/supabase-js@2.39.3'

/**
 * Verify that a resource belongs to the user's agency
 * @throws Error if resource not found or doesn't belong to agency
 */
export async function enforceTenantIsolation(
    token: string,
    tableName: string,
    resourceId: string,
    agencyId: string
): Promise<void> {
    const supabaseUrl = Deno.env.get('SUPABASE_URL')!
    const supabaseAnonKey = Deno.env.get('SUPABASE_ANON_KEY')!

    const supabase = createClient(supabaseUrl, supabaseAnonKey, {
        auth: {
            persistSession: false,
        },
        global: {
            headers: { Authorization: `Bearer ${token}` }
        }
    })

    const { data, error } = await supabase
        .from(tableName)
        .select('agency_id')
        .eq('id', resourceId)
        .single()

    if (error || !data) {
        throw new Error('NOT_FOUND')
    }

    if (data.agency_id !== agencyId) {
        throw new Error('FORBIDDEN')
    }
}

/**
 * Validate that agency_id is not present in request body
 * This enforces that agency context comes only from authenticated user
 */
export function rejectClientAgencyId(body: any): void {
    if (body && typeof body === 'object' && 'agency_id' in body) {
        throw new Error('VALIDATION_ERROR: agency_id must not be provided in request')
    }
}
