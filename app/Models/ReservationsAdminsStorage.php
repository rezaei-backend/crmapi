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
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function reservation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservations_id');
    }
}
