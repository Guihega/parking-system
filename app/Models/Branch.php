<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'is_active',
    ];

    public function tariffs()
    {
        return $this->hasMany(Tariff::class);
    }
}
