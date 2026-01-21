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
    ];

    protected $casts = [
        'is_employee' => 'boolean',
    ];

    public function okrs(): HasMany
    {
        return $this->hasMany(Okr::class, 'okr_type_id');
    }

    public function scopeForEmployees($query)
    {
        return $query->where('is_employee', true);
    }

    public function scopeForOrgUnits($query)
    {
        return $query->where('is_employee', false);
    }
}
