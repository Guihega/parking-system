<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    protected $table = 'ticket_statuses';
    public $timestamps = true; // tu tabla trae created_at/updated_at

    protected $fillable = ['code', 'name'];
}
