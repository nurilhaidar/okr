<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
        'owner_type',
        'owner_id',
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

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(Objective::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where(function ($q) use ($employeeId) {
            $q->where('owner_type', Employee::class)
              ->where('owner_id', $employeeId)
              ->orWhereHas('owner', function ($subQ) use ($employeeId) {
                  if ($subQ instanceof OrgUnit) {
                      $subQ->whereHas('employees', function ($empQ) use ($employeeId) {
                          $empQ->where('employee_id', $employeeId);
                      });
                  }
              });
        });
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
}
