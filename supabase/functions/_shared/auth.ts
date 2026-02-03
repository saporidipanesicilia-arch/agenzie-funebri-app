// Shared authentication utilities for Edge Functions
// Extracts and verifies user context from JWT

import { createClient } from 'https://esm.sh/@supabase/supabase-js@2.39.3'

export interface UserContext {
    userId: string
    email: string
    agencyId: string
    branchId: string | null
    role: 'owner' | 'admin' | 'operator' | 'viewer'
}

/**
 * Extract authenticated user from request
 * @throws Error if auth header is missing or invalid
 */
export async function getAuthenticatedUser(req: Request): Promise<{ userId: string; token: string }> {
    const authHeader = req.headers.get('Authorization')

    if (!authHeader?.startsWith('Bearer ')) {
        throw new Error('AUTH_REQUIRED')
    }

    const token = authHeader.substring(7)

    // Verify JWT using Supabase
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

    const { data: { user }, error } = await supabase.auth.getUser(token)

    if (error || !user) {
        throw new Error('AUTH_REQUIRED')
    }

    return {
        userId: user.id,
        token
    }
}

/**
 * Fetch full user context including agency and role
 * @throws Error if user profile not found
 */
export async function getUserContext(req: Request): Promise<UserContext> {
    const { userId, token } = await getAuthenticatedUser(req)

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

    const { data: userProfile, error } = await supabase
        .from('users')
        .select('agency_id, branch_id, email, role')
        .eq('auth_user_id', userId)
        .eq('is_active', true)
        .is('deleted_at', null)
        .single()

    if (error || !userProfile) {
        throw new Error('USER_PROFILE_NOT_FOUND')
    }

    return {
        userId,
        email: userProfile.email,
        agencyId: userProfile.agency_id,
        branchId: userProfile.branch_id,
        role: userProfile.role
    }
}

/**
 * Check if user has minimum required role
 */
export function hasRole(userRole: string, requiredRole: 'owner' | 'admin' | 'operator' | 'viewer'): boolean {
    const hierarchy = {
        'owner': 4,
        'admin': 3,
        'operator': 2,
        'viewer': 1
    }

    return (hierarchy[userRole as keyof typeof hierarchy] || 0) >= hierarchy[requiredRole]
}
