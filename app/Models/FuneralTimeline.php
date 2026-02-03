<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FuneralTimeline extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'funeral_timeline';

    protected $fillable = [
        'uuid',
        'funeral_id',
        'timeline_step_id',
        'assigned_user_id',
        'status',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($timeline) {
            if (empty($timeline->uuid)) {
                $timeline->uuid = (string) Str::uuid();
            }
        });

        // Auto-set started_at when status changes to in_progress
        static::updating(function ($timeline) {
            if ($timeline->isDirty('status')) {
                if ($timeline->status === 'in_progress' && !$timeline->started_at) {
                    $timeline->started_at = now();
                }
                if ($timeline->status === 'completed' && !$timeline->completed_at) {
                    $timeline->completed_at = now();
                }
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
     * Relazione: riferimento al template step
     */
    public function timelineStep()
    {
        return $this->belongsTo(TimelineStep::class);
    }

    /**
     * Relazione: utente assegnato
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Scope: step pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: step in progress
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope: step completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: step assigned to user
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    /**
     * Check if step is editable
     */
    public function isEditable(): bool
    {
        return !in_array($this->status, ['completed', 'skipped']);
    }

    /**
     * Get duration in hours
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return (int) $this->started_at->diffInHours($this->completed_at);
    }
}
