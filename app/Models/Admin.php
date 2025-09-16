<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'fname',
        'lname',
        'username',
        'password',
        'old_password',
        'enabled',
    ];

    protected $hidden = [
        'password',
        'old_password',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function reservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function visits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function callcenterCalls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CallcenterCall::class);
    }

    public function reservationsAdminsStorage(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReservationsAdminsStorage::class);
    }
}
