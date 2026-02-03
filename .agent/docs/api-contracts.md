# API Contracts — Funeral Management System

> **Status**: Contract Definition  
> **Architecture**: Clean Architecture (Use Case Driven)  
> **Multi-Tenancy**: All endpoints are tenant-scoped

---

## 1. TIMELINE WIZARD (Funeral Creation)

### 1.1 Store Funeral with Wizard Data

**Endpoint**: `POST /api/funerals`

**Authorization**: 
- Authenticated user
- Must belong to tenant
- Role: `operator` or `admin`

**Input DTO**:
```php
class StoreFuneralRequest
{
    public int $tenant_id;          // Auto-injected from auth
    
    // Step 1: Deceased Data
    public string $deceased_name;
    public string $deceased_surname;
    public string $deceased_tax_code;
    public string $deceased_birth_date;
    public string $deceased_birth_city;
    public string $deceased_death_date;
    public string $deceased_death_city;
    
    // Step 2: Ceremony Type
    public string $ceremony_type;    // 'burial' | 'cremation'
    public ?string $ceremony_location;
    public ?string $ceremony_date;
    
    // Step 3: Selected Products (from Memorial Table)
    public array $product_ids;       // [1, 5, 7]
    
    // Step 4: Document Checklist
    public array $required_documents; // ['certificate_death', 'cremation_request']
    
    // Optional
    public ?string $notes;
}
```

**Output DTO**:
```php
class StoreFuneralResponse
{
    public int $funeral_id;
    public string $funeral_code;     // Human-readable code (e.g., "FUN-2026-001")
    public string $status;           // 'draft'
    public array $timeline_steps;    // Initial timeline
    public float $estimated_total;   // Sum of products
    public string $created_at;
}
```

**Error Cases**:
- `422 Validation Failed`: Missing required fields or invalid data
- `403 Forbidden`: User doesn't belong to tenant
- `409 Conflict`: Tax code already exists for active funeral in tenant
- `500 Internal Error`: Database transaction failure

---

### 1.2 Save Wizard Draft

**Endpoint**: `POST /api/funerals/drafts`

**Authorization**: Same as 1.1

**Input DTO**:
```php
class SaveFuneralDraftRequest
{
    public int $tenant_id;
    public ?int $draft_id;           // null for new, int for update
    public array $wizard_data;       // Entire form state (JSON)
    public int $current_step;        // 1-5
}
```

**Output DTO**:
```php
class SaveFuneralDraftResponse
{
    public int $draft_id;
    public string $expires_at;       // 7 days from creation
}
```

**Error Cases**:
- `422 Validation Failed`: Invalid wizard_data structure
- `403 Forbidden`: Tenant mismatch
- `404 Not Found`: Draft ID doesn't exist

---

### 1.3 Retrieve Wizard Draft

**Endpoint**: `GET /api/funerals/drafts/{draft_id}`

**Authorization**: Same as 1.1, must own draft

**Output DTO**:
```php
class RetrieveFuneralDraftResponse
{
    public int $draft_id;
    public array $wizard_data;
    public int $current_step;
    public string $created_at;
    public string $expires_at;
}
```

**Error Cases**:
- `404 Not Found`: Draft doesn't exist
- `403 Forbidden`: Draft belongs to different tenant

---

## 2. MEMORIAL TABLE (Product Selection)

### 2.1 List Products by Category

**Endpoint**: `GET /api/products?category={category}`

**Authorization**: 
- Authenticated user
- Tenant-scoped products only

**Query Parameters**:
- `category`: `coffins` | `urns` | `flowers` | `accessories`
- `page`: int (default 1)
- `per_page`: int (default 20)

**Output DTO**:
```php
class ListProductsResponse
{
    public array $products;          // Array of ProductDTO
    public int $total;
    public int $current_page;
    public int $last_page;
}

class ProductDTO
{
    public int $id;
    public string $name;
    public string $type;             // 'coffin', 'urn', 'flowers'
    public float $price;
    public ?string $image_url;
    public string $description;
    public bool $in_stock;
}
```

**Error Cases**:
- `422 Validation Failed`: Invalid category
- `403 Forbidden`: User not authenticated

---

### 2.2 Get Product Details

**Endpoint**: `GET /api/products/{product_id}`

**Authorization**: Same as 2.1

**Output DTO**:
```php
class ProductDetailResponse
{
    public int $id;
    public string $name;
    public string $type;
    public float $price;
    public string $description;
    public ?string $image_url;
    public array $specifications;    // ['material' => 'Oak', 'finish' => 'Glossy']
    public bool $in_stock;
    public ?int $stock_quantity;
}
```

**Error Cases**:
- `404 Not Found`: Product doesn't exist or belongs to different tenant

---

## 3. FAMILY CLOUD

### 3.1 Authenticate Family Access

**Endpoint**: `POST /api/family/auth`

**Authorization**: None (public endpoint)

**Input DTO**:
```php
class FamilyAuthRequest
{
    public string $access_token;     // Generated token from agency
}
```

**Output DTO**:
```php
class FamilyAuthResponse
{
    public string $session_token;    // JWT or session ID
    public int $funeral_id;
    public string $deceased_name;
    public string $expires_at;       // Token expiration
}
```

**Error Cases**:
- `401 Unauthorized`: Invalid or expired access token
- `429 Too Many Requests`: Rate limit exceeded

---

### 3.2 Get Family Dashboard

**Endpoint**: `GET /api/family/dashboard`

**Authorization**: 
- Bearer token from 3.1
- Token must be active and not expired

**Output DTO**:
```php
class FamilyDashboardResponse
{
    public DeceasedInfoDTO $deceased;
    public CeremonyInfoDTO $ceremony;
    public array $documents;         // Array of DocumentDTO
    public array $photos;            // Array of PhotoDTO
    public int $condolences_count;
}

class DeceasedInfoDTO
{
    public string $full_name;
    public string $birth_date;
    public string $death_date;
}

class CeremonyInfoDTO
{
    public ?string $location;
    public ?string $date;
    public ?string $type;            // 'burial' | 'cremation'
}

class DocumentDTO
{
    public int $id;
    public string $name;             // 'Certificato di Morte'
    public string $type;             // 'certificate_death'
    public string $file_size;        // '1.2 MB'
    public string $download_url;     // Signed URL
}

class PhotoDTO
{
    public int $id;
    public string $thumbnail_url;
    public string $full_url;
    public string $uploaded_at;
}
```

**Error Cases**:
- `401 Unauthorized`: Invalid or expired token
- `404 Not Found`: Funeral not found

---

### 3.3 Download Document

**Endpoint**: `GET /api/family/documents/{document_id}/download`

**Authorization**: Same as 3.2, document must belong to funeral

**Output**: Binary file stream (PDF)

**Error Cases**:
- `401 Unauthorized`: Invalid token
- `403 Forbidden`: Document doesn't belong to token's funeral
- `404 Not Found`: Document not found

---

### 3.4 Upload Photo

**Endpoint**: `POST /api/family/photos`

**Authorization**: Same as 3.2

**Input DTO**:
```php
class UploadPhotoRequest
{
    public int $funeral_id;          // From token
    public file $photo;              // Max 10MB, jpg|png
    public ?string $caption;
}
```

**Output DTO**:
```php
class UploadPhotoResponse
{
    public int $photo_id;
    public string $thumbnail_url;
    public string $uploaded_at;
}
```

**Error Cases**:
- `422 Validation Failed`: File too large or invalid format
- `401 Unauthorized`: Invalid token
- `507 Insufficient Storage`: Storage quota exceeded

---

## 4. CEMETERIES & CEMETERY REGISTER

### 4.1 List Cemeteries

**Endpoint**: `GET /api/cemeteries`

**Authorization**: 
- Authenticated user
- Tenant-scoped

**Query Parameters**:
- `search`: string (optional, search by name)
- `page`: int
- `per_page`: int

**Output DTO**:
```php
class ListCemeteriesResponse
{
    public array $cemeteries;        // Array of CemeteryDTO
    public int $total;
}

class CemeteryDTO
{
    public int $id;
    public string $name;
    public string $location;
    public int $total_graves;
    public int $occupied_graves;
    public float $occupancy_percentage;
    public int $expiring_concessions_30d; // Alerts count
}
```

**Error Cases**:
- `403 Forbidden`: Not authenticated

---

### 4.2 Get Cemetery Details with Map

**Endpoint**: `GET /api/cemeteries/{cemetery_id}`

**Authorization**: Same as 4.1, must belong to tenant

**Output DTO**:
```php
class CemeteryDetailResponse
{
    public int $id;
    public string $name;
    public string $location;
    public ?array $map_coordinates;  // GeoJSON or coordinates
    public array $areas;             // Array of CemeteryAreaDTO
    public array $graves;            // Array of GraveDTO
    public int $total_capacity;
    public int $occupied_count;
}

class CemeteryAreaDTO
{
    public int $id;
    public string $name;             // 'Settore A'
    public int $capacity;
    public int $occupied;
}

class GraveDTO
{
    public int $id;
    public string $grave_number;
    public string $type;             // 'burial' | 'niche' | 'ossuary'
    public string $status;           // 'occupied' | 'available' | 'reserved'
    public ?string $occupant_name;
    public ?string $concession_expires_at;
    public ?array $position;         // [x, y] for map
}
```

**Error Cases**:
- `404 Not Found`: Cemetery doesn't exist or belongs to different tenant
- `403 Forbidden`: Tenant mismatch

---

### 4.3 Reserve Grave

**Endpoint**: `POST /api/cemeteries/{cemetery_id}/graves/{grave_id}/reserve`

**Authorization**: 
- Authenticated user
- Tenant-scoped
- Role: `operator` or `admin`

**Input DTO**:
```php
class ReserveGraveRequest
{
    public int $funeral_id;
    public int $concession_years;    // Typically 10, 20, 30, 99 (perpetual)
    public ?string $notes;
}
```

**Output DTO**:
```php
class ReserveGraveResponse
{
    public int $grave_id;
    public string $status;           // 'reserved'
    public string $concession_expires_at;
    public float $concession_fee;    // Calculated based on years
}
```

**Error Cases**:
- `409 Conflict`: Grave already occupied or reserved
- `422 Validation Failed`: Invalid concession_years
- `404 Not Found`: Grave or cemetery not found
- `403 Forbidden`: Funeral doesn't belong to tenant

---

### 4.4 List Expiring Concessions

**Endpoint**: `GET /api/cemeteries/expiring-concessions?days={days}`

**Authorization**: Same as 4.1

**Query Parameters**:
- `days`: int (default 30, filter by days until expiration)

**Output DTO**:
```php
class ExpiringConcessionsResponse
{
    public array $concessions;       // Array of ExpiringConcessionDTO
    public int $total;
}

class ExpiringConcessionDTO
{
    public int $grave_id;
    public string $grave_number;
    public string $cemetery_name;
    public string $occupant_name;
    public string $expires_at;
    public int $days_until_expiration;
    public ?string $relative_contact;
}
```

**Error Cases**:
- `422 Validation Failed`: Invalid days parameter

---

## Common Error Response Format

All endpoints return errors in this format:

```php
class ErrorResponse
{
    public string $message;          // Human-readable message
    public string $error_code;       // Machine-readable code
    public ?array $validation_errors; // Field-level errors (422 only)
    public int $status_code;
}
```

Example:
```json
{
    "message": "The deceased tax code has already been taken.",
    "error_code": "TAX_CODE_DUPLICATE",
    "validation_errors": {
        "deceased_tax_code": ["The deceased tax code has already been taken."]
    },
    "status_code": 422
}
```

---

## Multi-Tenancy Implementation

All endpoints implement tenant isolation via:

1. **Middleware**: `TenantScopeMiddleware` auto-injects `tenant_id` from auth
2. **Global Scopes**: Eloquent models use `BelongsToTenant` trait
3. **Repository Layer**: Validates tenant ownership before operations
4. **Use Cases**: Explicitly check tenant_id in requests

---

## Rate Limiting

- **Authenticated endpoints**: 60 requests/minute per user
- **Family Cloud (public)**: 10 requests/minute per IP
- **File uploads**: 5 requests/minute per user

---

## Next Steps

1. ✅ Contracts defined
2. ⏳ Implement Request/Response DTOs
3. ⏳ Implement Use Cases
4. ⏳ Implement Repositories
5. ⏳ Wire Controllers
6. ⏳ Add validation rules
7. ⏳ Write integration tests
