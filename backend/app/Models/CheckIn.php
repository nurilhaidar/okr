<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckIn extends Model
{
    use HasFactory;

    protected $table = 'check_in';

    protected $fillable = [
        'objective_id',
        'date',
        'current_value',
        'comments',
        'evidence_path',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'current_value' => 'float',
        ];
    }

    public function objective(): BelongsTo
    {
        return $this->belongsTo(Objective::class);
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'check_in_id');
    }

    public function scopeForObjective($query, $objectiveId)
    {
        return $query->where('objective_id', $objectiveId);
    }

    public function scopeOrderByDate($query)
    {
        return $query->orderBy('date', 'desc');
    }

    /**
     * Check if the check-in has any pending approval
     */
    public function hasPendingApproval(): bool
    {
        return $this->approvalLogs()->pending()->exists();
    }

    /**
     * Check if the check-in has been approved (latest log is approved)
     */
    public function isApproved(): bool
    {
        $latestLog = $this->latestApprovalLog();
        return $latestLog && $latestLog->isApproved();
    }

    /**
     * Check if the check-in has been rejected (latest log is rejected)
     */
    public function isRejected(): bool
    {
        $latestLog = $this->latestApprovalLog();
        return $latestLog && $latestLog->isRejected();
    }

    /**
     * Check if the check-in is still pending (latest log is pending)
     */
    public function isPending(): bool
    {
        $latestLog = $this->latestApprovalLog();
        return $latestLog && $latestLog->isPending();
    }

    /**
     * Get the latest approval log
     */
    public function latestApprovalLog(): ?ApprovalLog
    {
        return $this->approvalLogs()->latest()->first();
    }

    /**
     * Check if the check-in can be edited (only if no approved or rejected logs)
     */
    public function canBeEdited(): bool
    {
        return !$this->isApproved() && !$this->isRejected();
    }

    /**
     * Submit for approval - creates a pending approval log
     */
    public function submitForApproval(): void
    {
        // Only create pending log if there isn't already one
        if (!$this->hasPendingApproval()) {
            $this->approvalLogs()->create([
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Approve the check-in - creates an approved approval log
     * Only approvers can do this
     */
    public function approve(): void
    {
        $this->approvalLogs()->create([
            'status' => 'approved',
        ]);
    }

    /**
     * Reject the check-in - creates a rejected approval log
     * Only approvers can do this
     */
    public function reject(): void
    {
        $this->approvalLogs()->create([
            'status' => 'rejected',
        ]);
    }

    /**
     * Get the current status based on the latest approval log
     */
    public function getCurrentStatusAttribute(): string
    {
        $latestLog = $this->latestApprovalLog();
        if (!$latestLog) {
            return 'draft';
        }
        return $latestLog->status;
    }
}
