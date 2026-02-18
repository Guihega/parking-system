<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $table = 'tariffs_v2';

    protected $fillable = [
        'branch_id',
        'vehicle_type_id',
        'name',
        'description',
        'calc_type',
        'price_per_hour',
        'flat_amount',
        'grace_minutes',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
        'days_of_week',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
        'flat_amount'    => 'decimal:2',
        'grace_minutes'  => 'integer',
        'priority'       => 'integer',
        'is_active'      => 'boolean',
        'start_time'     => 'datetime:H:i',
        'end_time'       => 'datetime:H:i',
        'start_date'     => 'date',
        'end_date'       => 'date',
    ];

    /* ==========================================================
     | Relaciones
     ========================================================== */

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    /* ==========================================================
     | Scopes (clave para Admin)
     ========================================================== */

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeForBranches($query, $branchIds)
    {
        return $query->whereIn('branch_id', $branchIds);
    }

    /* ==========================================================
     | Helpers de dominio
     ========================================================== */

    public function isHourly(): bool
    {
        return $this->calc_type === 'hourly';
    }

    public function isFlat(): bool
    {
        return $this->calc_type === 'flat';
    }

    public function effectivePrice(): ?float
    {
        return $this->isHourly()
            ? $this->price_per_hour
            : $this->flat_amount;
    }
}
