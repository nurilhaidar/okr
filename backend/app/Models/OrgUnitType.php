<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrgUnitType extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $table = 'orgunit_type';

    protected $fillable = ['name'];

    public function orgUnits(): HasMany
    {
        return $this->hasMany(OrgUnit::class, 'orgunit_type_id');
    }
}
