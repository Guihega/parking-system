<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'vehicle_type_id',
        'parking_space_id',
        'plate',
        'entry_time',
        'exit_time',
        'status_id',
        'total_amount',
        'created_by',
        'token'
    ];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time'  => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function parkingSpace(): BelongsTo
    {
        return $this->belongsTo(ParkingSpace::class, 'parking_space_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

/*     public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    } */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
