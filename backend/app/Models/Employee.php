<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'employee';

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'rank_id',
        'position_id',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function orgUnits(): BelongsToMany
    {
        return $this->belongsToMany(OrgUnit::class, 'orgunit_employee', 'employee_id', 'orgunit_id')
            ->withPivot('orgunit_role_id')
            ->withTimestamps();
    }
}
