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
        'start_date',
        'end_date',
        'last_check_in_date',
        'next_check_in_due',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
            'target_value' => 'float',
            'deadline' => 'datetime',
            'start_date' => 'date',
            'end_date' => 'date',
            'last_check_in_date' => 'datetime',
            'next_check_in_due' => 'datetime',
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
     * Calculate progress percentage based on the sum of all approved check-ins
     */
    public function getProgressAttribute(): float
    {
        // Get all approved check-ins and sum their current_value
        $totalCurrentValue = $this->checkIns()
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
            ->sum('current_value');

        $targetValue = $this->target_value;

        // Avoid division by zero
        if ($targetValue == 0) {
            return $totalCurrentValue > 0 ? 100.0 : 0.0;
        }

        $progress = ($totalCurrentValue / $targetValue) * 100;

        // Cap at 100%
        return min($progress, 100.0);
    }

    /**
     * Get the sum of current values from all approved check-ins
     */
    public function getCurrentValueAttribute(): float
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
            ->sum('current_value') ?? 0.0;
    }
}
