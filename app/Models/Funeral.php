<?php

namespace App\Models;

use App\Infrastructure\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Funeral extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'agency_id',
        'branch_id',
        'service_type',
        'status',
        'ceremony_date',
        'ceremony_location',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'ceremony_date' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($funeral) {
            if (empty($funeral->uuid)) {
                $funeral->uuid = (string) Str::uuid();
            }

            // Set default status
            if (empty($funeral->status)) {
                $funeral->status = 'draft';
            }
        });

        // When a funeral is created, initialize timeline from template
        static::created(function ($funeral) {
            $funeral->initializeTimeline();
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
     * Initialize timeline from agency template steps
     */
    public function initializeTimeline(): void
    {
        $templateSteps = TimelineStep::where('agency_id', $this->agency_id)
            ->orderBy('order')
            ->get();

        foreach ($templateSteps as $step) {
            FuneralTimeline::create([
                'funeral_id' => $this->id,
                'timeline_step_id' => $step->id,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Relazione: un funerale appartiene a una sede
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relazione: un funerale ha un defunto
     */
    public function deceased()
    {
        return $this->hasOne(Deceased::class);
    }

    /**
     * Relazione: un funerale ha molti familiari
     */
    public function relatives()
    {
        return $this->hasMany(Relative::class);
    }

    /**
     * Relazione: contatto principale della famiglia
     */
    public function primaryContact()
    {
        return $this->hasOne(Relative::class)->where('is_primary_contact', true);
    }

    /**
     * Relazione: timeline steps di questo funerale
     */
    public function timeline()
    {
        return $this->hasMany(FuneralTimeline::class)->orderBy('id');
    }

    /**
     * Relazione: documenti di questo funerale
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get required documents for this funeral's service type
     */
    public function getRequiredDocumentsAttribute()
    {
        return DocumentType::where('agency_id', $this->agency_id)
            ->requiredForServiceType($this->service_type)
            ->get();
    }

    /**
     * Get missing required documents
     */
    public function getMissingDocumentsAttribute()
    {
        $required = $this->required_documents;
        $uploaded = $this->documents()->pluck('document_type_id')->toArray();

        return $required->filter(function ($docType) use ($uploaded) {
            return !in_array($docType->id, $uploaded);
        });
    }

    /**
     * Check if all required documents are approved
     */
    public function hasAllDocumentsApproved(): bool
    {
        $requiredCount = $this->required_documents->count();
        if ($requiredCount === 0) {
            return true; // No documents required
        }

        $approvedCount = $this->documents()
            ->whereIn('document_type_id', $this->required_documents->pluck('id'))
            ->where('status', 'approved')
            ->count();

        return $approvedCount >= $requiredCount;
    }

    /**
     * Get document checklist status
     */
    public function getDocumentChecklistAttribute(): array
    {
        $checklist = [];

        foreach ($this->required_documents as $docType) {
            $document = $this->documents()
                ->where('document_type_id', $docType->id)
                ->latest()
                ->first();

            $checklist[] = [
                'document_type' => $docType,
                'document' => $document,
                'status' => $document ? $document->status : 'missing',
                'is_uploaded' => $document !== null,
                'is_approved' => $document && $document->status === 'approved',
            ];
        }

        return $checklist;
    }

    /**
     * Relazione: preventivi di questo funerale
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Get active quote (if any)
     */
    public function getActiveQuoteAttribute()
    {
        return $this->quotes()
            ->whereIn('status', ['draft', 'sent', 'accepted'])
            ->latest()
            ->first();
    }

    /**
     * Relazione: token di accesso famiglia
     */
    public function familyAccessTokens()
    {
        return $this->hasMany(FamilyAccessToken::class);
    }

    /**
     * Get active family access token
     */
    public function getActiveFamilyAccessTokenAttribute()
    {
        return $this->familyAccessTokens()
            ->valid()
            ->latest()
            ->first();
    }

    /**
     * Scope: funerali attivi
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: funerali completati
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: funerali per tipo di servizio
     */
    public function scopeByServiceType($query, string $type)
    {
        return $query->where('service_type', $type);
    }

    /**
     * Check if funeral is editable
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'active']);
    }

    /**
     * Get timeline completion percentage
     */
    public function getCompletionPercentageAttribute(): int
    {
        $total = $this->timeline()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->timeline()->where('status', 'completed')->count();
        return (int) round(($completed / $total) * 100);
    }
}
