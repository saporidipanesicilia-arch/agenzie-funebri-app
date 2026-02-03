// Store Funeral Edge Function
// Creates a new funeral with deceased, relatives, and timeline

import { getUserContext } from '../_shared/auth.ts'
import { rejectClientAgencyId } from '../_shared/tenant.ts'
import { getSupabaseClient } from '../_shared/db.ts'
import { handleError, successResponse, SafeError } from '../_shared/errors.ts'

interface DeceasedInput {
    first_name: string
    last_name: string
    date_of_birth: string
    date_of_death: string
    place_of_birth?: string
    place_of_death?: string
    fiscal_code?: string
    gender?: 'M' | 'F' | 'other'
}

interface FuneralInput {
    funeral_code: string
    ceremony_type: 'religious' | 'civil' | 'none'
    ceremony_date?: string
    ceremony_location?: string
    disposition_type?: 'burial' | 'cremation' | 'donation'
    notes?: string
}

interface RelativeInput {
    first_name: string
    last_name: string
    relationship: string
    phone?: string
    email?: string
    is_primary_contact?: boolean
}

interface RequestBody {
    deceased: DeceasedInput
    funeral: FuneralInput
    relatives?: RelativeInput[]
}

Deno.serve(async (req) => {
    try {
        // Only allow POST
        if (req.method !== 'POST') {
            return new Response('Method Not Allowed', { status: 405 })
        }

        // Get authenticated user context
        const userContext = await getUserContext(req)
        const { agencyId, userId, role } = userContext

        // Check permissions (at least operator)
        if (role === 'viewer') {
            throw new SafeError(
                'INSUFFICIENT_PERMISSIONS',
                'Solo gli operatori possono creare funerali.',
                403
            )
        }

        // Parse request body
        const body: RequestBody = await req.json()

        // Security: reject if client tries to set agency_id
        rejectClientAgencyId(body.deceased)
        rejectClientAgencyId(body.funeral)

        // Validate required fields
        if (!body.deceased?.first_name || !body.deceased?.last_name) {
            throw new SafeError('VALIDATION_ERROR', 'Nome e cognome del defunto sono obbligatori.')
        }

        if (!body.funeral?.funeral_code) {
            throw new SafeError('VALIDATION_ERROR', 'Codice funerale obbligatorio.')
        }

        // Get authenticated client
        const { token } = await getUserContext(req)
        const supabase = getSupabaseClient(token)

        // Start transaction by using RPC or manual steps
        // Since Supabase JS doesn't have explicit transactions, we do sequential inserts
        // and rely on RLS for security

        // 1. Insert deceased
        const { data: deceased, error: deceasedError } = await supabase
            .from('deceased')
            .insert({
                first_name: body.deceased.first_name,
                last_name: body.deceased.last_name,
                date_of_birth: body.deceased.date_of_birth,
                date_of_death: body.deceased.date_of_death,
                place_of_birth: body.deceased.place_of_birth,
                place_of_death: body.deceased.place_of_death,
                fiscal_code: body.deceased.fiscal_code,
                gender: body.deceased.gender,
            })
            .select()
            .single()

        if (deceasedError) {
            console.error('Failed to insert deceased:', deceasedError)
            throw deceasedError
        }

        // 2. Insert funeral (agency_id is enforced by RLS and insert policy)
        const currentYear = new Date().getFullYear()

        const { data: funeral, error: funeralError } = await supabase
            .from('funerals')
            .insert({
                agency_id: agencyId,
                deceased_id: deceased.id,
                funeral_code: body.funeral.funeral_code,
                status: 'planning',
                ceremony_type: body.funeral.ceremony_type,
                ceremony_date: body.funeral.ceremony_date,
                ceremony_location: body.funeral.ceremony_location,
                disposition_type: body.funeral.disposition_type,
                notes: body.funeral.notes,
                created_by_user_id: userId,
            })
            .select()
            .single()

        if (funeralError) {
            console.error('Failed to insert funeral:', funeralError)

            // Cleanup deceased if funeral fails
            await supabase.from('deceased').delete().eq('id', deceased.id)

            throw funeralError
        }

        // 3. Insert relatives
        if (body.relatives && body.relatives.length > 0) {
            const relativesData = body.relatives.map(rel => ({
                funeral_id: funeral.id,
                first_name: rel.first_name,
                last_name: rel.last_name,
                relationship: rel.relationship,
                phone: rel.phone,
                email: rel.email,
                is_primary_contact: rel.is_primary_contact || false,
            }))

            const { error: relativesError } = await supabase
                .from('relatives')
                .insert(relativesData)

            if (relativesError) {
                console.error('Failed to insert relatives:', relativesError)
                // Continue anyway - relatives are optional
            }
        }

        // 4. Initialize timeline from template
        // Get timeline steps for this agency
        const { data: timelineSteps, error: stepsError } = await supabase
            .from('timeline_steps')
            .select('*')
            .eq('agency_id', agencyId)
            .order('display_order', { ascending: true })

        if (!stepsError && timelineSteps && timelineSteps.length > 0) {
            const funeralDate = new Date(funeral.created_at)

            const timelineData = timelineSteps.map(step => {
                const scheduledDate = new Date(funeralDate)
                scheduledDate.setHours(scheduledDate.getHours() + (step.hours_offset || 0))

                return {
                    funeral_id: funeral.id,
                    timeline_step_id: step.id,
                    scheduled_at: scheduledDate.toISOString(),
                    status: 'pending',
                }
            })

            const { error: timelineError } = await supabase
                .from('funeral_timelines')
                .insert(timelineData)

            if (timelineError) {
                console.error('Failed to initialize timeline:', timelineError)
                // Continue anyway - can be added later
            }
        }

        // Success response
        return successResponse({
            funeral_id: funeral.id,
            funeral_code: funeral.funeral_code,
            deceased_id: deceased.id,
            status: funeral.status,
            created_at: funeral.created_at,
        }, 201)

    } catch (error) {
        return handleError(error)
    }
})
