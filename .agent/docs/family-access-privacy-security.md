# Family Access System - Privacy & Security Considerations

## Overview
The family access system allows controlled, privacy-compliant access to funeral information via secure tokens and QR codes.

---

## Security Architecture

### Token Security

#### 1. Token Generation
```php
// Generate random 64-character token
$plainToken = Str::random(64);

// SHA-256 hash for database storage
$hashedToken = hash('sha256', $plainToken);

// Encrypt plain token for later display
$encryptedPlain = Crypt::encryptString($plainToken);

// Store both
$accessToken->token = $hashedToken;  // For validation
$accessToken->token_plain = $encryptedPlain;  // For display/email
```

**Why this approach?**
- Plain token never stored in database (like passwords)
- SHA-256 hash prevents rainbow table attacks
- Encrypted plain token allows regenerating access URL
- 64 characters = 2^256 possible combinations (brute-force resistant)

#### 2. Token Validation
```php
// User provides plain token (from URL or QR)
$plainToken = 'abc123...';

// Hash it
$hashedToken = hash('sha256', $plainToken);

// Find in database
$accessToken = FamilyAccessToken::where('token', $hashedToken)->first();

// Validate
if ($accessToken && $accessToken->isValid()) {
    // Grant access
}
```

---

## Privacy Considerations (GDPR Compliance)

### 1. Data Minimization

**Only expose necessary data based on access level:**

```php
// FULL access
$data = [
    'funeral' => [
        'deceased_name' => $funeral->deceased->full_name,
        'ceremony_date' => $funeral->ceremony_date,
        'ceremony_location' => $funeral->ceremony_location,
    ],
    'timeline' => $funeral->timeline,  // Progress tracking
    'documents' => $funeral->documents()->approved()->get(),  // Only approved
    'quote' => [
        'total' => $funeral->active_quote->final_total,  // NO cost prices
        'items' => $funeral->active_quote->items->map(fn($item) => [
            'description' => $item->description,
            'quantity' => $item->quantity,
            'selling_price' => $item->selling_price,  // NO cost_price
        ]),
    ],
    'cemetery' => $funeral->deceased->burial->concession ?? null,
];

// LIMITED access
$data = [
    'funeral' => [
        'deceased_name' => $funeral->deceased->full_name,
        'ceremony_date' => $funeral->ceremony_date,
        'ceremony_location' => $funeral->ceremony_location,
    ],
    'timeline' => $funeral->timeline->map(fn($step) => [
        'description' => $step->description,
        'status' => $step->status,
        // NO internal notes
    ]),
    // NO documents, NO quote, NO cemetery
];

// DOCUMENTS_ONLY access
$data = [
    'documents' => $funeral->documents()
        ->approved()  // Only approved
        ->whereNotIn('document_type_id', [/* sensitive types */])
        ->get(),
    // NO funeral details, NO timeline, NO quote
];

// CEMETERY_ONLY access
$data = [
    'cemetery' => [
        'cemetery_name' => $burial->grave->cemeteryArea->cemetery->name,
        'area_name' => $burial->grave->cemeteryArea->name,
        'grave_number' => $burial->grave->grave_number,
        'concession_expiry' => $burial->concession->expiry_date,
        // NO financial data, NO other burials
    ],
];
```

### 2. Field-Level Filtering

**NEVER expose:**
```php
// Internal fields
$funeral->internal_notes  // ❌
$funeral->agency_id  // ❌
$funeral->created_by_user_id  // ❌

// Staff information
$timeline->completed_by_user_id  // ❌
$document->uploaded_by_user->name  // ❌ (show "Operatore" instead)

// Financial details (unless authorized)
$quoteItem->cost_price  // ❌
$quote->margin_percentage  // ❌

// Other relatives (unless explicitly shared)
$funeral->relatives()->where('id', '!=', $tokenOwner)->get()  // ❌

// Rejected/pending documents
$document->where('status', 'rejected')->get()  // ❌

// Other funerals
Funeral::where('agency_id', $funeral->agency_id)->get()  // ❌
```

**Always anonymize:**
```php
// Instead of staff names
$timeline->completed_by_user->name  // "Giovanni Rossi"
// Show:
"Operatore dell'agenzia"  // ✅

// Instead of exact timestamps
$document->approved_at  // "2024-02-02 14:35:22"
// Show:
$document->approved_at->diffForHumans()  // "2 giorni fa" ✅
```

### 3. IP Logging & Data Retention

**Access logging (GDPR Article 32):**
```php
// Log every access for security
FamilyAccessLog::create([
    'family_access_token_id' => $token->id,
    'accessed_at' => now(),
    'ip_address' => request()->ip(),  // For security monitoring
    'user_agent' => request()->userAgent(),  // Detect anomalies
    'accessed_resource' => 'documents',
    'action' => 'download',
]);
```

**Data retention policy:**
```php
// Delete old logs after 2 years (configurable)
FamilyAccessLog::where('accessed_at', '<', now()->subYears(2))->delete();

// Delete expired tokens after grace period
FamilyAccessToken::expired()
    ->where('expires_at', '<', now()->subDays(60))
    ->delete();
```

---

## Expiration Logic

### Automatic Expiration

```php
// On token creation
$token = FamilyAccessToken::create([
    'expires_at' => now()->addDays(30),  // Expires in 30 days
]);

// Check if expired
$token->isExpired();  // true/false

// Validation
$token->isValid();  // Checks: not expired + not revoked + not exceeded max uses
```

### Expiration Statuses

```
🟢 VALID (days_until_expiry > 7)
   Token is active and valid
   No action needed

🟡 EXPIRING SOON (days_until_expiry <= 7)
   Token will expire soon
   Send reminder to family

🔴 EXPIRED (days_until_expiry < 0)
   Token is no longer valid
   Access denied
```

### Extension

```php
// Family requests extension
$token = FamilyAccessToken::find($id);
$token->extend(30);  // Add 30 more days

// Result:
// expires_at = original_expiry + 30 days
```

---

## Usage Limits

### Max Uses

```php
// Create token with usage limit
$token = FamilyAccessToken::create([
    'max_uses' => 5,  // Can be used 5 times
]);

// Each access increments counter
$token->recordAccess('documents', 'view');
$token->current_uses++;  // Now 1

// After 5 uses
$token->hasExceededMaxUses();  // true
$token->isValid();  // false (blocked)
```

**Use cases:**
- One-time document download: `max_uses = 1`
- Family review (multiple visits): `max_uses = 10`
- Unlimited access: `max_uses = null`

---

## Revocation

### Manual Revocation

```php
// Agency revokes token
$token->revoke(
    userId: auth()->id(),
    reason: 'Richiesta dalla famiglia'
);

// Result:
// is_active = false
// revoked_at = now()
// revoked_by_user_id = {userId}
// Access immediately denied
```

### Auto-Revocation Triggers

```php
// 1. Suspicious activity (too many failed attempts)
if ($failedAttempts > 10) {
    $token->revoke($systemUserId, 'Tentativi sospetti');
}

// 2. Family request via email
if ($familyRequestsRevocation) {
    $token->revoke($adminUserId, 'Richiesta del titolare');
}

// 3. Funeral completed + grace period
if ($funeral->status === 'completed' && now()->diffInDays($funeral->updated_at) > 90) {
    $funeral->familyAccessTokens()->each->revoke($systemUserId, 'Funerale completato');
}
```

---

## QR Code Security

### QR Code Generation

```php
// Generate on token creation
QrCode::format('png')
    ->size(300)
    ->margin(2)
    ->generate($token->access_url);

// Store in private storage
Storage::put("family-qr/{$funeral->id}/{$token->uuid}.png", $qrCode);
```

### QR Code Access

```php
// Serve via authenticated route
Route::get('/admin/family-token/{token}/qr', function (FamilyAccessToken $token) {
    // Verify user has permission
    if (!auth()->user()->can('view', $token)) {
        abort(403);
    }

    // Return QR code
    return Storage::download($token->qr_code_path);
})->middleware('auth');
```

**Security notes:**
- QR codes stored in private storage (not public)
- Only agency staff can download QR
- QR contains same token as URL (no separate secret)

---

## Audit Trail

### What is Logged

```php
// Every access is logged
FamilyAccessLog::create([
    'family_access_token_id' => $token->id,
    'accessed_at' => now(),
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'accessed_resource' => 'document',  // What was accessed
    'action' => 'download',  // What action was performed
    'resource_id' => $document->id,  // Which specific document
]);
```

### Audit Reports

```php
// Get all access for a funeral
$logs = FamilyAccessLog::whereHas('accessToken', function ($query) use ($funeral) {
        $query->where('funeral_id', $funeral->id);
    })
    ->with('accessToken')
    ->orderBy('accessed_at', 'desc')
    ->get();

// Statistics
$totalAccesses = $logs->count();
$uniqueIPs = $logs->pluck('ip_address')->unique()->count();
$documentDownloads = $logs->where('action', 'download')->count();

// Timeline of access
/*
2024-02-02 10:30 - Maria Verdi - Visualizzato dettagli funerale (IP: 192.168.1.10)
2024-02-02 10:35 - Maria Verdi - Visualizzato timeline (IP: 192.168.1.10)
2024-02-02 10:40 - Maria Verdi - Scaricato certificato morte (IP: 192.168.1.10)
2024-02-03 15:20 - Maria Verdi - Visualizzato preventivo (IP: 192.168.1.10)
*/
```

---

## Real-World Privacy Scenarios

### Scenario 1: Controlling Quote Visibility

**Problem:** Family should see final prices, but NOT agency costs.

**Solution:**
```php
// Filter quote data
$quote = $funeral->active_quote;

$filteredQuote = [
    'quote_number' => $quote->quote_number,
    'total' => $quote->final_total,  // ✅ Final price
    'items' => $quote->items->map(fn($item) => [
        'description' => $item->description,
        'quantity' => $item->quantity,
        'selling_price' => $item->selling_price,  // ✅ Selling price
        // HIDE: cost_price, margin_percentage
    ]),
    // HIDE: discount_percentage, cost breakdown
];
```

### Scenario 2: Multiple Family Members

**Problem:** Different family members need different access levels.

**Solution:**
```php
// Token 1: Spouse (full access)
$token1 = FamilyAccessToken::create([
    'funeral_id' => $funeral->id,
    'access_level' => 'full',
    'granted_to_name' => 'Coniuge',
    'granted_to_email' => 'spouse@example.com',
]);

// Token 2: Adult child (limited access, no finances)
$token2 = FamilyAccessToken::create([
    'funeral_id' => $funeral->id,
    'access_level' => 'limited',
    'granted_to_name' => 'Figlio',
    'granted_to_email' => 'child@example.com',
]);

// Token 3: Extended family (cemetery info only)
$token3 = FamilyAccessToken::create([
    'funeral_id' => $funeral->id,
    'access_level' => 'cemetery_only',
    'granted_to_name' => 'Famiglia estesa',
    'granted_to_email' => 'extended@example.com',
    'expires_at' => null,  // Never expires (grave location)
]);
```

### Scenario 3: Document Download Tracking

**Problem:** Need to prove certificate was delivered to family (legal compliance).

**Solution:**
```php
// Family downloads document
$token->recordAccess(
    resource: 'document',
    action: 'download',
    resourceId: $document->id,
    details: [
        'document_name' => $document->file_name,
        'document_type' => $document->documentType->name,
    ]
);

// Later: Prove delivery
$downloadLog = FamilyAccessLog::where('family_access_token_id', $token->id)
    ->where('accessed_resource', 'document')
    ->where('resource_id', $document->id)
    ->where('action', 'download')
    ->first();

// Evidence:
// "Certificato di morte scaricato da Maria Verdi il 2024-02-02 alle 10:40 (IP: 192.168.1.10)"
```

---

## GDPR Rights Implementation

### Right to Access (Article 15)
```php
// Family can access their own data via token
// No account creation required
// Transparent access to what agency has
```

### Right to Rectification (Article 16)
```php
// Family can request data correction via contact form
// Agency verifies and updates
// Change logged in audit trail
```

### Right to Erasure (Article 17)
```php
// Family requests data deletion
$token->revoke($userId, 'Richiesta cancellazione dati');

// After funeral completion + retention period
$funeral->delete();  // Soft delete with grace period
```

### Right to Restriction (Article 18)
```php
// Temporarily restrict access without full deletion
$token->update(['is_active' => false]);

// Can be re-enabled later
$token->update(['is_active' => true]);
```

### Right to Data Portability (Article 20)
```php
// Export all funeral data in machine-readable format
$export = [
    'funeral' => $funeral->toArray(),
    'deceased' => $funeral->deceased->toArray(),
    'documents' => $funeral->documents->toArray(),
    'timeline' => $funeral->timeline->toArray(),
];

return response()->json($export)->download('funeral-data.json');
```

---

## Best Practices

### ✅ DO
- Generate long, random tokens (64+ characters)
- Hash tokens before database storage
- Log all access for audit trail
- Filter data based on access level
- Anonymize staff information
- Set reasonable expiration dates (default 30 days)
- Delete expired tokens after grace period
- Use HTTPS only (token in URL)

### ❌ DON'T
- Store plain tokens in database
- Expose internal notes to family
- Show cost prices or margins
- Share tokens across multiple funerals
- Allow unlimited access without expiration (except specific cases)
- Log sensitive data in access logs
- Send tokens via unencrypted email (use HTTPS links)

---

## Future Enhancements

- 📧 **Email Notifications** - Family notified of ceremony changes
- 🔐 **2FA for Sensitive Downloads** - SMS code for death certificate
- 📱 **Mobile App Support** - Push notifications
- 🔗 **Single Sign-On** - Link multiple tokens to family account
- 🔍 **Advanced Analytics** - Family engagement metrics
- 🌐 **Multi-Language Support** - Translate portal to family's language

---

**Result:** Secure, privacy-compliant family access! 🔒
