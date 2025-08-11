<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationsAdminsStorage extends Model
{
    protected $table = 'reservations_admins_storage';

    protected $fillable = [
        'admin_id',
        'reservations_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
