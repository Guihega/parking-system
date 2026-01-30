<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['code', 'name', 'module', 'is_active'];

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
}
