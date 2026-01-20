<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgUnitEmployee extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $table = 'orgunit_employee';

    protected $fillable = [
        'orgunit_id',
        'employee_id',
        'orgunit_role_id',
    ];

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'orgunit_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function orgUnitRole(): BelongsTo
    {
        return $this->belongsTo(OrgUnitRole::class, 'orgunit_role_id');
    }
}
