<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrgUnitRole extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $table = 'orgunit_role';

    protected $fillable = ['name'];

    public function orgUnitEmployees(): HasMany
    {
        return $this->hasMany(OrgUnitEmployee::class, 'orgunit_role_id');
    }
}
