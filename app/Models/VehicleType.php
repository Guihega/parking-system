<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $table = 'vehicle_types';

    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Cajones compatibles con este tipo de vehículo
     */
    public function parkingSpaces()
    {
        return $this->hasMany(ParkingSpace::class, 'vehicle_type_id');
    }
}
