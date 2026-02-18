<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSpaceStatus extends Model
{
    protected $table = 'parking_space_statuses';

    protected $fillable = [
        'code',
        'name',
    ];

    public function parkingSpaces()
    {
        return $this->hasMany(ParkingSpace::class, 'status_id');
    }
}
