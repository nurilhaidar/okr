<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
