// Assign Resources to Funeral Edge Function
// Assigns team and vehicle to a funeral

import { getUserContext, hasRole } from '../_shared/auth.ts'
import { getSupabaseClient } from '../_shared/db.ts'
import { handleError, successResponse, SafeError } from '../_shared/errors.ts'

interface RequestBody {
    funeral_id: string
    team_id?: string
    vehicle_id?: string
    assignment_date: string
    notes?: string
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
                'Solo gli operatori possono assegnare risorse.',
                403
            )
        }

        const body: RequestBody = await req.json()

        if (!body.funeral_id || !body.assignment_date) {
            throw new SafeError('VALIDATION_ERROR', 'ID funerale e data assegnazione obbligatori.')
        }

        if (!body.team_id && !body.vehicle_id) {
            throw new SafeError('VALIDATION_ERROR', 'Specificare almeno un team o un veicolo.')
        }

        const supabase = getSupabaseClient(token)

        // Verify funeral belongs to agency
        const { data: funeral, error: funeralError } = await supabase
            .from('funerals')
            .select('id, agency_id')
            .eq('id', body.funeral_id)
            .single()

        if (funeralError || !funeral) {
            throw new Error('NOT_FOUND')
        }

        if (funeral.agency_id !== agencyId) {
            throw new Error('FORBIDDEN')
        }

        // Verify team belongs to agency (if specified)
        if (body.team_id) {
            const { data: team, error: teamError } = await supabase
                .from('teams')
                .select('id, agency_id')
                .eq('id', body.team_id)
                .single()

            if (teamError || !team || team.agency_id !== agencyId) {
                throw new SafeError('VALIDATION_ERROR', 'Team non trovato o non appartiene alla tua agenzia.')
            }
        }

        // Verify vehicle belongs to agency (if specified)
        if (body.vehicle_id) {
            const { data: vehicle, error: vehicleError } = await supabase
                .from('vehicles')
                .select('id, agency_id, status')
                .eq('id', body.vehicle_id)
                .single()

            if (vehicleError || !vehicle || vehicle.agency_id !== agencyId) {
                throw new SafeError('VALIDATION_ERROR', 'Veicolo non trovato o non appartiene alla tua agenzia.')
            }

            if (vehicle.status !== 'available') {
                throw new SafeError('RESOURCE_UNAVAILABLE', 'Il veicolo non è disponibile.')
            }
        }

        // Check for existing assignments on the same date (simple conflict check)
        const assignmentDate = new Date(body.assignment_date)
        const startOfDay = new Date(assignmentDate)
        startOfDay.setHours(0, 0, 0, 0)
        const endOfDay = new Date(assignmentDate)
        endOfDay.setHours(23, 59, 59, 999)

        if (body.vehicle_id) {
            const { data: existingAssignments } = await supabase
                .from('funeral_assignments')
                .select('id')
                .eq('vehicle_id', body.vehicle_id)
                .gte('assignment_date', startOfDay.toISOString())
                .lte('assignment_date', endOfDay.toISOString())
                .limit(1)

            if (existingAssignments && existingAssignments.length > 0) {
                throw new SafeError('CONFLICT', 'Il veicolo è già assegnato in questa data.')
            }
        }

        // Insert assignment
        const { data: assignment, error: assignmentError } = await supabase
            .from('funeral_assignments')
            .insert({
                funeral_id: body.funeral_id,
                team_id: body.team_id,
                vehicle_id: body.vehicle_id,
                assignment_date: body.assignment_date,
                notes: body.notes,
                assigned_by_user_id: userId
            })
            .select()
            .single()

        if (assignmentError) {
            console.error('Failed to create assignment:', assignmentError)
            throw assignmentError
        }

        return successResponse({
            assignment_id: assignment.id,
            assigned_at: assignment.created_at,
            funeral_id: assignment.funeral_id
        }, 201)

    } catch (error) {
        return handleError(error)
    }
})
