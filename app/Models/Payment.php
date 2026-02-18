<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'tenant_id',
        'ticket_id',
        'cash_session_id',
        'payment_type_code',
        'amount',
    ];

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }
}
