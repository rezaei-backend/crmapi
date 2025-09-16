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
        'reason',
    ];

    protected $casts = [
        'com_state' => 'boolean',
        'status' => 'integer',
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function adminsStorage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ReservationsAdminsStorage::class, 'reservations_id');
    }
}
