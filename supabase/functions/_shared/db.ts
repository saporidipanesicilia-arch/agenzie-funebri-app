// Database client helpers

import { createClient, SupabaseClient } from 'https://esm.sh/@supabase/supabase-js@2.39.3'

/**
 * Create authenticated Supabase client
 * This client will respect RLS policies using the user's JWT
 */
export function getSupabaseClient(token: string): SupabaseClient {
    const supabaseUrl = Deno.env.get('SUPABASE_URL')!
    const supabaseAnonKey = Deno.env.get('SUPABASE_ANON_KEY')!

    return createClient(supabaseUrl, supabaseAnonKey, {
        auth: {
            persistSession: false,
        },
        global: {
            headers: { Authorization: `Bearer ${token}` }
        }
    })
}

/**
 * Create service role client (bypasses RLS)
 * Use with extreme caution and only when necessary
 */
export function getServiceRoleClient(): SupabaseClient {
    const supabaseUrl = Deno.env.get('SUPABASE_URL')!
    const supabaseServiceKey = Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!

    return createClient(supabaseUrl, supabaseServiceKey, {
        auth: {
            persistSession: false,
            autoRefreshToken: false
        }
    })
}
