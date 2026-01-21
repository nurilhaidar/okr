<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Objective extends Model
{
    use HasFactory;

    protected $table = 'objective';

    protected $fillable = [
        'okr_id',
        'description',
        'weight',
        'target_type',
        'target_value',
        'deadline',
        'tracking_type',
        'tracker',
        'approver',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
            'target_value' => 'float',
            'deadline' => 'datetime',
        ];
    }

    public function okr(): BelongsTo
    {
        return $this->belongsTo(Okr::class);
    }

    public function trackerEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'tracker');
    }

    public function approverEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver');
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class);
    }

    public function scopeByOkr($query, $okrId)
    {
        return $query->where('okr_id', $okrId);
    }

    public function scopeForTracker($query, $employeeId)
    {
        return $query->where('tracker', $employeeId);
    }

    public function scopeForApprover($query, $employeeId)
    {
        return $query->where('approver', $employeeId);
    }

    public function scopePending($query)
    {
        return $query->where('deadline', '>', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('deadline', '<=', now());
    }

    /**
     * Get the latest approved check-in for this objective
     * Returns the most recent check-in that has been approved (not pending or rejected)
     */
    public function latestApprovedCheckIn(): ?CheckIn
    {
        return $this->checkIns()
            ->whereHas('approvalLogs', function ($query) {
                // Find check-ins where the LATEST approval log has status 'approved'
                $query->whereIn('id', function ($subQuery) {
                    $subQuery->select(DB::raw('MAX(id)'))
                        ->from('approval_log')
                        ->whereColumn('check_in_id', 'check_in.id')
                        ->groupBy('check_in_id');
                })
                ->where('status', 'approved');
            })
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Calculate progress percentage based on the latest approved check-in
     */
    public function getProgressAttribute(): float
    {
        $latestCheckIn = $this->latestApprovedCheckIn();

        if (!$latestCheckIn) {
            return 0.0;
        }

        $currentValue = $latestCheckIn->current_value;
        $targetValue = $this->target_value;

        // Avoid division by zero
        if ($targetValue == 0) {
            return $currentValue > 0 ? 100.0 : 0.0;
        }

        $progress = ($currentValue / $targetValue) * 100;

        // Cap at 100%
        return min($progress, 100.0);
    }

    /**
     * Get the current value from the latest approved check-in
     */
    public function getCurrentValueAttribute(): float
    {
        $latestCheckIn = $this->latestApprovedCheckIn();
        return $latestCheckIn ? $latestCheckIn->current_value : 0.0;
    }
}
