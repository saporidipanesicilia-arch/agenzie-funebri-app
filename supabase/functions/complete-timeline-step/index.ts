// Complete Timeline Step Edge Function
// Marks a timeline step as completed

import { getUserContext, hasRole } from '../_shared/auth.ts'
import { enforceTenantIsolation } from '../_shared/tenant.ts'
import { getSupabaseClient } from '../_shared/db.ts'
import { handleError, successResponse, SafeError } from '../_shared/errors.ts'

interface RequestBody {
    funeral_timeline_id: string
    notes?: string
}

Deno.serve(async (req) => {
    try {
        if (req.method !== 'POST') {
            return new Response('Method Not Allowed', { status: 405 })
        }

        // Get authenticated user context
        const userContext = await getUserContext(req)
        const { agencyId, userId, role, token } = userContext

        // Check permissions (at least operator)
        if (!hasRole(role, 'operator')) {
            throw new SafeError(
                'INSUFFICIENT_PERMISSIONS',
                'Solo gli operatori possono completare i passaggi della timeline.',
                403
            )
        }

        const body: RequestBody = await req.json()

        if (!body.funeral_timeline_id) {
            throw new SafeError('VALIDATION_ERROR', 'ID del passaggio timeline obbligatorio.')
        }

        const supabase = getSupabaseClient(token)

        // Get the timeline step with funeral info to verify tenant
        const { data: timelineStep, error: fetchError } = await supabase
            .from('funeral_timelines')
            .select(`
        *,
        funeral:funerals (
          id,
          agency_id,
          status
        )
      `)
            .eq('id', body.funeral_timeline_id)
            .single()

        if (fetchError || !timelineStep) {
            throw new Error('NOT_FOUND')
        }

        // Verify belongs to user's agency
        if (timelineStep.funeral.agency_id !== agencyId) {
            throw new Error('FORBIDDEN')
        }

        // Check if already completed
        if (timelineStep.status === 'completed') {
            throw new SafeError(
                'VALIDATION_ERROR',
                'Questo passaggio è già stato completato.',
                400
            )
        }

        // Update timeline step
        const { data: updated, error: updateError } = await supabase
            .from('funeral_timelines')
            .update({
                status: 'completed',
                completed_at: new Date().toISOString(),
                completed_by_user_id: userId,
                notes: body.notes,
                updated_at: new Date().toISOString()
            })
            .eq('id', body.funeral_timeline_id)
            .select()
            .single()

        if (updateError) {
            console.error('Failed to update timeline step:', updateError)
            throw updateError
        }

        // Get next pending step (if any)
        const { data: nextStep } = await supabase
            .from('funeral_timelines')
            .select(`
        *,
        timeline_step:timeline_steps (
          name,
          description
        )
      `)
            .eq('funeral_id', timelineStep.funeral_id)
            .eq('status', 'pending')
            .order('scheduled_at', { ascending: true })
            .limit(1)
            .single()

        return successResponse({
            success: true,
            funeral_timeline_id: updated.id,
            completed_at: updated.completed_at,
            next_step: nextStep ? {
                id: nextStep.id,
                name: nextStep.timeline_step?.name,
                scheduled_at: nextStep.scheduled_at
            } : null
        })

    } catch (error) {
        return handleError(error)
    }
})
