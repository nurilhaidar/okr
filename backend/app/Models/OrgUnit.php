<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrgUnit extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $table = 'orgunit';

    protected $fillable = [
        'name',
        'custom_type',
        'orgunit_type_id',
        'parent_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(OrgUnitType::class, 'orgunit_type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'parent_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'orgunit_employee')
            ->withPivot('orgunit_role_id')
            ->withTimestamps();
    }
}
