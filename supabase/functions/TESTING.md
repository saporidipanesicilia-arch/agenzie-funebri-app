# Edge Functions Testing Guide

## Local Development Setup

### 1. Install Supabase CLI

```bash
npm install -g supabase
```

### 2. Start Local Supabase

```bash
cd "C:\Users\tigno\Desktop\APP\agenzie funebri"
supabase start
```

This will start:
- PostgreSQL database
- Kong API Gateway
- Edge Functions runtime (Deno)

### 3. Serve Functions Locally

```bash
supabase functions serve
```

Functions will be available at: `http://localhost:54321/functions/v1/<function-name>`

---

## Testing with cURL

### Prerequisites
Get your JWT token:
1. Login to your app or use Supabase Dashboard
2. Copy the `access_token` from the Auth response

### 1. Store Funeral

```bash
curl -X POST 'http://localhost:54321/functions/v1/store-funeral' \
  -H 'Authorization: Bearer YOUR_JWT_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "deceased": {
      "first_name": "Mario",
      "last_name": "Rossi",
      "date_of_birth": "1950-01-15",
      "date_of_death": "2026-02-01",
      "place_of_death": "Milano",
      "gender": "M"
    },
    "funeral": {
      "funeral_code": "F2026001",
      "ceremony_type": "religious",
      "ceremony_date": "2026-02-05T14:00:00Z",
      "ceremony_location": "Chiesa San Carlo",
      "disposition_type": "burial"
    },
    "relatives": [
      {
        "first_name": "Luigi",
        "last_name": "Rossi",
        "relationship": "son",
        "phone": "+39 333 1234567",
        "email": "luigi.rossi@example.com",
        "is_primary_contact": true
      }
    ]
  }'
```

**Expected Response:**
```json
{
  "funeral_id": "uuid-here",
  "funeral_code": "F2026001",
  "deceased_id": "uuid-here",
  "status": "planning",
  "created_at": "2026-02-03T23:00:00Z"
}
```

---

### 2. Complete Timeline Step

```bash
curl -X POST 'http://localhost:54321/functions/v1/complete-timeline-step' \
  -H 'Authorization: Bearer YOUR_JWT_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "funeral_timeline_id": "TIMELINE_UUID_HERE",
    "notes": "Ritiro completato senza problemi"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "funeral_timeline_id": "uuid",
  "completed_at": "2026-02-03T23:00:00Z",
  "next_step": {
    "id": "uuid",
    "name": "Vestizione e Tanatoestetica",
    "scheduled_at": "2026-02-04T11:00:00Z"
  }
}
```

---

### 3. Assign Resources

```bash
curl -X POST 'http://localhost:54321/functions/v1/assign-resources' \
  -H 'Authorization: Bearer YOUR_JWT_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "funeral_id": "FUNERAL_UUID_HERE",
    "team_id": "TEAM_UUID_HERE",
    "vehicle_id": "VEHICLE_UUID_HERE",
    "assignment_date": "2026-02-05T10:00:00Z",
    "notes": "Assegnazione standard"
  }'
```

**Expected Response:**
```json
{
  "assignment_id": "uuid",
  "assigned_at": "2026-02-03T23:00:00Z",
  "funeral_id": "uuid"
}
```

---

### 4. Register Cemetery Death

```bash
curl -X POST 'http://localhost:54321/functions/v1/register-cemetery-death' \
  -H 'Authorization: Bearer YOUR_JWT_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "funeral_id": "FUNERAL_UUID_HERE",
    "grave_id": "GRAVE_UUID_HERE",
    "burial_type": "inhumation",
    "burial_date": "2026-02-05T16:00:00Z",
    "notes": "Settore Nord, Fila 3"
  }'
```

**Expected Response:**
```json
{
  "burial_id": "uuid",
  "grave_number": "L-12",
  "cemetery_name": "Cimitero Maggiore",
  "burial_date": "2026-02-05T16:00:00Z",
  "occupancy": "1/1"
}
```

---

### 5. Generate Family Token

```bash
curl -X POST 'http://localhost:54321/functions/v1/generate-family-token' \
  -H 'Authorization: Bearer YOUR_JWT_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "funeral_id": "FUNERAL_UUID_HERE",
    "expires_in_days": 30
  }'
```

**Expected Response:**
```json
{
  "token": "abc123def456...",
  "public_url": "https://yourapp.com/family-cloud?token=abc123def456...",
  "expires_at": "2026-03-05T23:00:00Z",
  "funeral": {
    "id": "uuid",
    "code": "F2026001",
    "deceased_name": "Mario Rossi"
  }
}
```

---

## Testing Error Cases

### 1. Unauthorized Access (No Token)

```bash
curl -X POST 'http://localhost:54321/functions/v1/store-funeral' \
  -H 'Content-Type: application/json' \
  -d '{"funeral": {}}'
```

**Expected:**
```json
{
  "error": true,
  "code": "AUTH_REQUIRED",
  "message": "Autenticazione richiesta. Effettua il login."
}
```

### 2. Insufficient Permissions (Viewer Role)

Login as a viewer, then:

```bash
curl -X POST 'http://localhost:54321/functions/v1/store-funeral' \
  -H 'Authorization: Bearer VIEWER_JWT_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"deceased": {"first_name": "Test"}, "funeral": {"funeral_code": "TEST"}}'
```

**Expected:**
```json
{
  "error": true,
  "code": "INSUFFICIENT_PERMISSIONS",
  "message": "Solo gli operatori possono creare funerali."
}
```

### 3. Cross-Tenant Access Attempt

Try to complete a timeline step from another agency:

```bash
curl -X POST 'http://localhost:54321/functions/v1/complete-timeline-step' \
  -H 'Authorization: Bearer YOUR_JWT_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "funeral_timeline_id": "OTHER_AGENCY_TIMELINE_UUID"
  }'
```

**Expected:**
```json
{
  "error": true,
  "code": "FORBIDDEN",
  "message": "Accesso negato. Non hai i permessi per questa risorsa."
}
```

---

## Production Deployment

### Deploy All Functions

```bash
# Deploy individual functions
supabase functions deploy store-funeral
supabase functions deploy complete-timeline-step
supabase functions deploy assign-resources
supabase functions deploy register-cemetery-death
supabase functions deploy generate-family-token

# Or deploy all at once
supabase functions deploy --no-verify-jwt
```

### Set Environment Variables

In Supabase Dashboard → Edge Functions → Settings:

```
PUBLIC_SITE_URL=https://yourapp.com
```

---

## Invoking from Frontend

### JavaScript/TypeScript

```typescript
import { createClient } from '@supabase/supabase-js'

const supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY)

// Get auth token automatically
const { data, error } = await supabase.functions.invoke('store-funeral', {
  body: {
    deceased: {
      first_name: 'Mario',
      last_name: 'Rossi',
      date_of_birth: '1950-01-15',
      date_of_death: '2026-02-01'
    },
    funeral: {
      funeral_code: 'F2026001',
      ceremony_type: 'religious'
    }
  }
})

if (error) {
  console.error('Error:', error.message)
} else {
  console.log('Funeral created:', data.funeral_id)
}
```

---

## Monitoring & Logs

### View Function Logs

```bash
# Local
supabase functions logs store-funeral

# Production (requires project ref)
supabase functions logs store-funeral --project-ref YOUR_PROJECT_REF
```

### Check Function Status

In Supabase Dashboard:
- Navigate to **Edge Functions**
- View invocation count, errors, and latency
- Access detailed logs for debugging
