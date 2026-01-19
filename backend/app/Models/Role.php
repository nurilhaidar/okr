<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $table = 'role';

    protected $fillable = ['name'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
