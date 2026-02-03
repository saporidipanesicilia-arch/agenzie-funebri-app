# Funeral Domain Module - Architecture Documentation

## Overview

The **Funeral Domain** is the core business module of the system. It manages funerals, deceased persons, relatives, and the configurable Timeline Wizard for tracking funeral progress.

---

## Domain Model

### Entity Relationships

```
Agency (Tenant)
  └── TimelineStep (template configurabile per tenant)
       
Agency
  └── Branch
       └── Funeral
            ├── Deceased (1:1)
            ├── Relatives (1:N)
            └── FuneralTimeline (1:N)
                 ├── TimelineStep (template reference)
                 └── AssignedUser
```

---

## Database Tables

### 1. `deceased`
Stores information about the deceased person.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| uuid | uuid | Public identifier |
| funeral_id | bigint | FK to funerals |
| first_name | string | Nome |
| last_name | string | Cognome |
| birth_date | date | Data di nascita |
| death_date | date | Data del decesso |
| place_of_birth | string | Luogo di nascita |
| place_of_death | string | Luogo del decesso |
| tax_code | string | Codice fiscale |

**Computed Attributes:**
- `full_name` - First + Last name
- `age_at_death` - Calculated from birth/death dates

---

### 2. `funerals`
Main funeral entity.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| uuid | uuid | Public identifier |
| agency_id | bigint | FK to agencies (tenant) |
| branch_id | bigint | FK to branches |
| service_type | enum | burial, entombment, cremation |
| status | enum | draft, active, completed, cancelled |
| ceremony_date | datetime | Data/ora cerimonia |
| ceremony_location | string | Luogo cerimonia |
| start_date | date | Inizio pratica |
| end_date | date | Fine pratica |

**Lifecycle:**
```
draft → active → completed
              ↘ cancelled
```

**Auto-behavior:**
- When created → `initializeTimeline()` copies template steps

**Computed Attributes:**
- `completion_percentage` - Progress based on timeline

---

### 3. `relatives`
Family members and contacts.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| uuid | uuid | Public identifier |
| funeral_id | bigint | FK to funerals |
| name | string | Nome completo |
| relation_type | string | Es. Coniuge, Figlio |
| phone | string | Telefono |
| email | string | Email |
| is_primary_contact | boolean | Contatto principale? |

**Scopes:**
- `primaryContacts()` - Filter only primary contacts

---

### 4. `timeline_steps` (Template)
Configurable step templates per agency.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| uuid | uuid | Public identifier |
| agency_id | bigint | FK to agencies (tenant-scoped) |
| name | string | Es. "Ritiro salma" |
| description | text | Descrizione step |
| order | integer | Ordine esecuzione |
| is_required | boolean | Step obbligatorio? |
| estimated_duration_hours | integer | Durata stimata |
| required_documents | json | Documenti richiesti (array) |

**Purpose:**
- Define **reusable templates** per agency
- Small agency: 6 simple steps
- Large agency: 13 detailed steps

**Scopes:**
- `required()` - Only required steps
- `ordered()` - Sorted by order

---

### 5. `funeral_timeline` (Instance)
Step instances for each funeral.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| uuid | uuid | Public identifier |
| funeral_id | bigint | FK to funerals |
| timeline_step_id | bigint | FK to timeline_steps (template) |
| assigned_user_id | bigint | FK to users (nullable) |
| status | enum | pending, in_progress, completed, skipped |
| started_at | datetime | When started |
| completed_at | datetime | When completed |

**Status Flow:**
```
pending → in_progress → completed
                     ↘ skipped
```

**Auto-behavior:**
- `started_at` auto-set when status → `in_progress`
- `completed_at` auto-set when status → `completed`

**Scopes:**
- `pending()`, `inProgress()`, `completed()`
- `assignedTo($userId)` - Filter by assigned user

**Computed Attributes:**
- `duration` - Hours between start and completion

---

## Models

### 1. `Funeral`
**Traits:** `BelongsToTenant`, `SoftDeletes`

**Relationships:**
```php
$funeral->branch          // BelongsTo Branch
$funeral->deceased        // HasOne Deceased
$funeral->relatives       // HasMany Relative
$funeral->primaryContact  // HasOne Relative (is_primary_contact)
$funeral->timeline        // HasMany FuneralTimeline
```

**Methods:**
- `initializeTimeline()` - Copy template steps when funeral is created
- `isEditable()` - Check if status allows editing
- `completion_percentage` - Calculate progress

**Scopes:**
- `active()` - Only active funerals
- `completed()` - Only completed
- `byServiceType($type)` - Filter by burial/cremation/etc

---

### 2. `Deceased`
**Traits:** `SoftDeletes`

**Relationships:**
```php
$deceased->funeral  // BelongsTo Funeral
```

**Computed Attributes:**
- `full_name` - "{first_name} {last_name}"
- `age_at_death` - Calculated age

---

### 3. `Relative`
**Traits:** `SoftDeletes`

**Relationships:**
```php
$relative->funeral  // BelongsTo Funeral
```

**Scopes:**
- `primaryContacts()` - Only is_primary_contact = true

---

### 4. `TimelineStep`
**Traits:** `BelongsToTenant`, `SoftDeletes`

**Relationships:**
```php
$step->funeralTimelines  // HasMany FuneralTimeline
```

**Scopes:**
- `required()` - Only required steps
- `ordered()` - By order field

---

### 5. `FuneralTimeline`
**Traits:** `SoftDeletes`

**Relationships:**
```php
$timeline->funeral       // BelongsTo Funeral
$timeline->timelineStep  // BelongsTo TimelineStep (template)
$timeline->assignedUser  // BelongsTo User
```

**Methods:**
- `isEditable()` - Check if status allows changes
- `duration` - Hours between start/completion

**Scopes:**
- `pending()`, `inProgress()`, `completed()`
- `assignedTo($userId)`

---

## Timeline Wizard Flow

### Setup Phase (Once per Agency)
1. Admin creates **Timeline Steps** (templates)
2. Each step has: name, order, description, is_required
3. Steps are **tenant-scoped** (agency_id)

### Funeral Creation
1. User creates a new Funeral
2. `Funeral::created` event fires
3. `initializeTimeline()` auto-called
4. Copies all agency template steps → `funeral_timeline` table
5. All steps start with `status = 'pending'`

### During Funeral
1. User views funeral timeline
2. Assigns steps to users
3. Updates status: pending → in_progress → completed
4. Auto-timestamps track progress
5. Dashboard shows completion percentage

---

## How This Supports Small vs Large Agencies

### Small Agency (Onoranze Funebri Rossi)

**Timeline Template:**
1. Ritiro salma
2. Vestizione
3. Preparazione documenti
4. Affissioni manifesti (optional)
5. Cerimonia
6. Sepoltura

**Workflow:**
- Simple linear flow
- 2 users total (owner + operator)
- Manual assignment
- Basic tracking

**Data Volume:**
- ~50 funerals/year
- Simple documents
- One branch

### Large Agency (Gruppo Funerario Lombardo)

**Timeline Template:**
1. Primo contatto famiglia
2. Ritiro salma
3. Tanatoprassi (optional)
4. Vestizione e trucco
5. Allestimento camera ardente (optional)
6. Documenti amministrativi
7. Stampa materiali
8. Affissioni
9. Coordinamento cerimonia
10. Cerimonia religiosa (optional)
11. Trasporto al cimitero
12. Sepoltura/Cremazione
13. Follow-up famiglia

**Workflow:**
- Parallel steps (multiple users)
- 6 users across 3 branches
- Automated assignment (future)
- Advanced reporting (future)

**Data Volume:**
- ~500 funerals/year
- Complex documents
- Multiple branches

---

## Key Design Decisions

### ✅ Configurable Templates
**Why:** Different agencies have different processes
**How:** `timeline_steps` tenant-scoped, copied to `funeral_timeline` on creation

### ✅ Instance vs Template
**Why:** Same template used for all funerals, but each has independent progress
**How:** `TimelineStep` (template) vs `FuneralTimeline` (instance)

### ✅ Auto-initialization
**Why:** Reduce manual work, ensure consistency
**How:** `Funeral::created` event → `initializeTimeline()`

### ✅ Soft Deletes Everywhere
**Why:** Funeral data is sensitive, recovery is critical
**How:** All models use `SoftDeletes` trait

### ✅ UUID for Public Routes
**Why:** Security - never expose internal IDs
**How:** All models use UUID for `getRouteKeyName()`

### ✅ No Multi-Tenant on Deceased/Relative
**Why:** They're scoped via Funeral, which has `agency_id`
**How:** Access control inherited through relationship

---

## Usage Examples

### Create a Funeral
```php
$funeral = Funeral::create([
    'agency_id' => auth()->user()->agency_id,
    'branch_id' => auth()->user()->branch_id,
    'service_type' => 'burial',
    'status' => 'draft',
]);

// Timeline auto-created via event
```

### Add Deceased
```php
$deceased = Deceased::create([
    'funeral_id' => $funeral->id,
    'first_name' => 'Mario',
    'last_name' => 'Rossi',
    'death_date' => today(),
]);
```

### Assign Timeline Step
```php
$timelineItem = $funeral->timeline()->first();
$timelineItem->update([
    'assigned_user_id' => $userId,
    'status' => 'in_progress', // Auto-sets started_at
]);
```

### Mark Step Complete
```php
$timelineItem->update([
    'status' => 'completed', // Auto-sets completed_at
    'notes' => 'Completed without issues',
]);
```

### Query Funerals
```php
// All active funerals for current tenant
Funeral::active()->get();

// Only burials
Funeral::byServiceType('burial')->get();

// User's assigned steps
FuneralTimeline::assignedTo(auth()->id())
    ->pending()
    ->get();
```

---

## Future Enhancements

- ⏳ Auto-assignment based on roles/workload
- ⏳ Email notifications on step assignment
- ⏳ Timeline branching (conditional steps)
- ⏳ Document attachments per step
- ⏳ Mobile app for field workers
- ⏳ Analytics dashboard (avg duration, bottlenecks)

---

## Files Created

```
database/migrations/
├── 2024_01_01_100001_create_deceased_table.php
├── 2024_01_01_100002_create_funerals_table.php
├── 2024_01_01_100003_create_relatives_table.php
├── 2024_01_01_100004_create_timeline_steps_table.php
└── 2024_01_01_100005_create_funeral_timeline_table.php

app/Models/
├── Deceased.php
├── Funeral.php
├── Relative.php
├── TimelineStep.php
└── FuneralTimeline.php

database/seeders/
├── TimelineStepSeeder.php
└── FuneralSeeder.php
```

---

**The Funeral Domain is now production-ready!** 🎉
