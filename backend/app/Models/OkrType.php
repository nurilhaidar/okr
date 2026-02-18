<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OkrType extends Model
{
    use HasFactory;

    protected $table = 'okr_type';

    protected $fillable = [
        'name',
        'is_employee',
        'is_active',
    ];

    protected $casts = [
        'is_employee' => 'boolean',
    ];

    protected $appends = [
        'employee_okrs_count',
        'orgunit_okrs_count',
    ];

    public function okrs(): HasMany
    {
        return $this->hasMany(Okr::class, 'okr_type_id');
    }

    public function scopeForEmployees($query)
    {
        return $query->whereHas('okrs', function ($q) {
            return $q->whereNotNull('employee_id');
        });
    }

    public function scopeForOrgUnits($query)
    {
        return $query->whereHas('okrs', function ($q) {
            return $q->whereNotNull('orgunit_id');
        });
    }

    /**
     * Get count of OKRs owned by employees for this type
     * Uses okr_type.is_employee to determine if we count employee OKRs
     */
    public function getEmployeeOkrsCountAttribute(): int
    {
        // If this is an employee OKR type, count OKRs with employee_id
        if (!$this->exists || !$this->is_employee) {
            return 0;
        }

        return $this->okrs()
            ->whereNotNull('employee_id')
            ->count();
    }

    /**
     * Get count of OKRs owned by org units for this type
     * Uses okr_type.is_employee to determine if we count org unit OKRs
     */
    public function getOrgunitOkrsCountAttribute(): int
    {
        // If this is not an employee OKR type (org unit), count OKRs with orgunit_id
        if ($this->exists && $this->is_employee) {
            return 0;
        }

        return $this->okrs()
            ->whereNotNull('orgunit_id')
            ->count();
    }
}
