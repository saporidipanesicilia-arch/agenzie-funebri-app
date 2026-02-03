// Register Cemetery Death Edge Function
// Records burial in cemetery grave

import { getUserContext, hasRole } from '../_shared/auth.ts'
import { getSupabaseClient } from '../_shared/db.ts'
import { handleError, successResponse, SafeError } from '../_shared/errors.ts'

interface RequestBody {
    funeral_id: string
    grave_id: string
    burial_type: 'inhumation' | 'entombment' | 'cremation_niche'
    burial_date: string
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
                'Solo gli operatori possono registrare sepolture.',
                403
            )
        }

        const body: RequestBody = await req.json()

        if (!body.funeral_id || !body.grave_id || !body.burial_type || !body.burial_date) {
            throw new SafeError('VALIDATION_ERROR', 'Dati incompleti.')
        }

        const supabase = getSupabaseClient(token)

        // Verify funeral belongs to agency
        const { data: funeral, error: funeralError } = await supabase
            .from('funerals')
            .select('id, agency_id, deceased_id')
            .eq('id', body.funeral_id)
            .single()

        if (funeralError || !funeral) {
            throw new Error('NOT_FOUND')
        }

        if (funeral.agency_id !== agencyId) {
            throw new Error('FORBIDDEN')
        }

        // Verify grave belongs to agency's cemetery
        const { data: grave, error: graveError } = await supabase
            .from('graves')
            .select(`
        *,
        cemetery_area:cemetery_areas (
          id,
          cemetery:cemeteries (
            id,
            agency_id,
            name
          )
        )
      `)
            .eq('id', body.grave_id)
            .single()

        if (graveError || !grave) {
            throw new SafeError('VALIDATION_ERROR', 'Loculo non trovato.')
        }

        if (grave.cemetery_area.cemetery.agency_id !== agencyId) {
            throw new SafeError('FORBIDDEN', 'Il loculo non appartiene alla tua agenzia.')
        }

        // Check grave capacity
        const { data: existingBurials, error: burialsError } = await supabase
            .from('burials')
            .select('id')
            .eq('grave_id', body.grave_id)
            .is('deleted_at', null)

        if (burialsError) {
            throw burialsError
        }

        const currentOccupancy = existingBurials?.length || 0
        if (currentOccupancy >= grave.capacity) {
            throw new SafeError('VALIDATION_ERROR', 'Il loculo ha raggiunto la capacità massima.')
        }

        // Insert burial
        const { data: burial, error: burialError } = await supabase
            .from('burials')
            .insert({
                grave_id: body.grave_id,
                deceased_id: funeral.deceased_id,
                funeral_id: body.funeral_id,
                burial_type: body.burial_type,
                burial_date: body.burial_date,
                notes: body.notes,
                registered_by_user_id: userId
            })
            .select()
            .single()

        if (burialError) {
            console.error('Failed to register burial:', burialError)
            throw burialError
        }

        // Update grave status if now full
        const newOccupancy = currentOccupancy + 1
        if (newOccupancy >= grave.capacity) {
            await supabase
                .from('graves')
                .update({ status: 'occupied' })
                .eq('id', body.grave_id)
        } else if (grave.status === 'available') {
            // Mark as partially occupied
            await supabase
                .from('graves')
                .update({ status: 'occupied' })
                .eq('id', body.grave_id)
        }

        return successResponse({
            burial_id: burial.id,
            grave_number: grave.grave_number,
            cemetery_name: grave.cemetery_area.cemetery.name,
            burial_date: burial.burial_date,
            occupancy: `${newOccupancy}/${grave.capacity}`
        }, 201)

    } catch (error) {
        return handleError(error)
    }
})
