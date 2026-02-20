<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Okr extends Model
{
    use HasFactory;

    protected $table = 'okr';

    protected $fillable = [
        'name',
        'weight',
        'okr_type_id',
        'start_date',
        'end_date',
        'employee_id',
        'orgunit_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function okrType(): BelongsTo
    {
        return $this->belongsTo(OkrType::class, 'okr_type_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'orgunit_id');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(Objective::class);
    }

    /**
     * Get the owner (employee or orgunit) based on which is set
     */
    public function getOwnerAttribute()
    {
        if ($this->employee_id) {
            return $this->employee;
        }
        return $this->orgUnit;
    }

    /**
     * Get the owner type as a string
     */
    public function getOwnerTypeAttribute(): ?string
    {
        if ($this->employee_id) {
            return 'employee';
        }
        if ($this->orgunit_id) {
            return 'orgunit';
        }
        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if OKR is currently active (considers both flag and end date)
     * Use this method to dynamically check if an OKR should be considered active
     */
    public function isCurrentlyActive(): bool
    {
        // If explicitly inactive, return false
        if (!$this->is_active) {
            return false;
        }

        // If end date has passed, consider it inactive
        if ($this->end_date && $this->end_date->startOfDay()->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Calculate overall progress percentage based on weighted average of objectives
     * Each objective's contribution is: (objective_progress * objective_weight)
     */
    public function getProgressAttribute(): float
    {
        $objectives = $this->objectives;

        if ($objectives->isEmpty()) {
            return 0.0;
        }

        $totalWeight = 0.0;
        $weightedProgress = 0.0;

        foreach ($objectives as $objective) {
            $objectiveWeight = (float) $objective->weight;
            $objectiveProgress = $objective->progress;

            $weightedProgress += ($objectiveProgress * $objectiveWeight);
            $totalWeight += $objectiveWeight;
        }

        // Avoid division by zero
        if ($totalWeight == 0) {
            return 0.0;
        }

        return $weightedProgress / $totalWeight;
    }

    /**
     * Check if total weight of all objectives equals 100% (1.0)
     */
    public function hasValidObjectiveWeights(): bool
    {
        $totalWeight = (float) $this->objectives->sum('weight');
        return abs($totalWeight - 1.0) < 0.0001; // Allow small floating point difference
    }

    /**
     * Get total weight of all objectives
     */
    public function getTotalObjectiveWeight(): float
    {
        return (float) $this->objectives->sum('weight');
    }
}
