<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSpace extends Model
{
    protected $table = 'parking_spaces';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'code',
        'floor',
        'section',
        'vehicle_type_id',
        'status_id',
    ];

    /* =========================
     | Relaciones
     ========================= */

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function status()
    {
        return $this->belongsTo(ParkingSpaceStatus::class, 'status_id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }
}
