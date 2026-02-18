<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'user_id',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'difference',
        'opened_at',
        'closed_at',
        'is_open',
        'observations',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_open' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
