<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';

    protected $fillable = [
        'user_id',
        'admin_id',
        'order_id',
        'reserv_city',
        'date',
        'time',
        'information',
        'com_state',
        'status',
        'replace_id',
    ];

    protected $casts = [
        'com_state' => 'boolean',
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
