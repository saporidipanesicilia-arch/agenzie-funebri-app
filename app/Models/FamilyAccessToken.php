<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FamilyAccessToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'funeral_id',
        'token',
        'token_plain',
        'access_level',
        'granted_to_name',
        'granted_to_email',
        'granted_to_phone',
        'expires_at',
        'max_uses',
        'current_uses',
        'is_active',
        'revoked_at',
        'revoked_by_user_id',
        'qr_code_path',
        'created_by_user_id',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_active' => 'boolean',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
    ];

    protected $hidden = [
        'token',  // Never expose hashed token
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($accessToken) {
            if (empty($accessToken->uuid)) {
                $accessToken->uuid = (string) Str::uuid();
            }

            // Generate token if not provided
            if (empty($accessToken->token_plain)) {
                $plainToken = static::generatePlainToken();
                $accessToken->token_plain = Crypt::encryptString($plainToken);
                $accessToken->token = hash('sha256', $plainToken);
            }
        });

        static::created(function ($accessToken) {
            // Generate QR code after creation
            $accessToken->generateQrCode();
        });

        static::deleting(function ($accessToken) {
            // Delete QR code file
            if ($accessToken->qr_code_path && Storage::exists($accessToken->qr_code_path)) {
                Storage::delete($accessToken->qr_code_path);
            }
        });
    }

    /**
     * Generate a random plain token
     */
    protected static function generatePlainToken(): string
    {
        return Str::random(64);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Relazione: funerale
     */
    public function funeral()
    {
        return $this->belongsTo(Funeral::class);
    }

    /**
     * Relazione: creato da
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Relazione: revocato da
     */
    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    /**
     * Relazione: access logs
     */
    public function accessLogs()
    {
        return $this->hasMany(FamilyAccessLog::class);
    }

    /**
     * Get decrypted plain token (for display/email)
     */
    public function getPlainTokenAttribute(): string
    {
        return Crypt::decryptString($this->token_plain);
    }

    /**
     * Get access URL
     */
    public function getAccessUrlAttribute(): string
    {
        return url('/family/' . $this->plain_token);
    }

    /**
     * Get QR code URL
     */
    public function getQrCodeUrlAttribute(): ?string
    {
        if (!$this->qr_code_path) {
            return null;
        }

        return Storage::url($this->qr_code_path);
    }

    /**
     * Generate QR code
     */
    public function generateQrCode(): bool
    {
        $qrPath = "family-qr/{$this->funeral_id}/" . $this->uuid . ".png";

        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($this->access_url);

        Storage::put($qrPath, $qrCode);

        return $this->update(['qr_code_path' => $qrPath]);
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;  // No expiration
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if token is revoked
     */
    public function isRevoked(): bool
    {
        return !$this->is_active || $this->revoked_at !== null;
    }

    /**
     * Check if token has exceeded max uses
     */
    public function hasExceededMaxUses(): bool
    {
        if (!$this->max_uses) {
            return false;  // No limit
        }

        return $this->current_uses >= $this->max_uses;
    }

    /**
     * Check if token is valid (not expired, not revoked, not exceeded)
     */
    public function isValid(): bool
    {
        return !$this->isExpired()
            && !$this->isRevoked()
            && !$this->hasExceededMaxUses();
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Get expiration status
     */
    public function getExpirationStatusAttribute(): string
    {
        if (!$this->expires_at) {
            return 'never_expires';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        $days = $this->days_until_expiry;

        if ($days <= 7) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    /**
     * Get access level label
     */
    public function getAccessLevelLabelAttribute(): string
    {
        return match ($this->access_level) {
            'full' => 'Accesso completo',
            'limited' => 'Accesso limitato',
            'documents_only' => 'Solo documenti',
            'cemetery_only' => 'Solo cimitero',
            default => 'Sconosciuto',
        };
    }

    /**
     * Increment usage count and log access
     */
    public function recordAccess(string $resource, string $action = 'view', ?int $resourceId = null, ?array $details = null): void
    {
        // Increment usage
        $this->increment('current_uses');

        // Log access
        FamilyAccessLog::create([
            'family_access_token_id' => $this->id,
            'accessed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'accessed_resource' => $resource,
            'action' => $action,
            'resource_id' => $resourceId,
            'details' => $details ? json_encode($details) : null,
        ]);
    }

    /**
     * Revoke token
     */
    public function revoke(int $userId, ?string $reason = null): bool
    {
        return $this->update([
            'is_active' => false,
            'revoked_at' => now(),
            'revoked_by_user_id' => $userId,
            'notes' => $reason ? $this->notes . "\n\nRevocato: " . $reason : $this->notes,
        ]);
    }

    /**
     * Extend expiration
     */
    public function extend(int $days): bool
    {
        if (!$this->expires_at) {
            // Already never expires
            return true;
        }

        $newExpiry = $this->expires_at->copy()->addDays($days);

        return $this->update(['expires_at' => $newExpiry]);
    }

    /**
     * Validate token by plain text
     */
    public static function validateToken(string $plainToken): ?self
    {
        $hashedToken = hash('sha256', $plainToken);

        $token = static::where('token', $hashedToken)->first();

        if (!$token || !$token->isValid()) {
            return null;
        }

        return $token;
    }

    /**
     * Scope: active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereNull('revoked_at');
    }

    /**
     * Scope: valid tokens (active + not expired + not exceeded)
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereColumn('current_uses', '<', 'max_uses');
            });
    }

    /**
     * Scope: expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope: expiring soon (< 7 days)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(7)]);
    }
}
