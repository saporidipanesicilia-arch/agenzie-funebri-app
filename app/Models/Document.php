<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'funeral_id',
        'document_type_id',
        'uploaded_by_user_id',
        'reviewed_by_user_id',
        'file_path',
        'file_name',
        'file_size_kb',
        'mime_type',
        'status',
        'rejection_reason',
        'version',
        'replaces_document_id',
        'expires_at',
        'approved_at',
        'rejected_at',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
        });

        // Auto-set approved_at when status changes to approved
        static::updating(function ($document) {
            if ($document->isDirty('status')) {
                if ($document->status === 'approved' && !$document->approved_at) {
                    $document->approved_at = now();
                }
                if ($document->status === 'rejected' && !$document->rejected_at) {
                    $document->rejected_at = now();
                }
            }
        });

        // Auto-delete file when document is deleted
        static::deleting(function ($document) {
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Relazione: appartiene a un funerale
     */
    public function funeral()
    {
        return $this->belongsTo(Funeral::class);
    }

    /**
     * Relazione: tipo documento
     */
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Relazione: uploaded by user
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    /**
     * Relazione: reviewed by user
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /**
     * Relazione: documento precedente (versioning)
     */
    public function replacesDocument()
    {
        return $this->belongsTo(Document::class, 'replaces_document_id');
    }

    /**
     * Relazione: nuove versioni di questo documento
     */
    public function newerVersions()
    {
        return $this->hasMany(Document::class, 'replaces_document_id');
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if document is editable
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['pending', 'submitted', 'rejected']);
    }

    /**
     * Check if document needs review
     */
    public function needsReview(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Approve document
     */
    public function approve(int $reviewerId, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'reviewed_by_user_id' => $reviewerId,
            'approved_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Reject document
     */
    public function reject(int $reviewerId, string $reason, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'reviewed_by_user_id' => $reviewerId,
            'rejection_reason' => $reason,
            'rejected_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        $kb = $this->file_size_kb;

        if ($kb < 1024) {
            return $kb . ' KB';
        }

        return round($kb / 1024, 2) . ' MB';
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'green',
            'submitted' => 'yellow',
            'rejected' => 'red',
            'expired' => 'orange',
            default => 'gray',
        };
    }

    /**
     * Get status label in Italian
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Da caricare',
            'submitted' => 'In revisione',
            'approved' => 'Approvato',
            'rejected' => 'Rifiutato',
            'expired' => 'Scaduto',
            default => 'Sconosciuto',
        };
    }

    /**
     * Scope: only approved documents
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: documents needing review
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope: rejected documents
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: expired documents
     */
    public function scopeExpired($query)
    {
        return $query->where('status', '!=', 'expired')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }
}
