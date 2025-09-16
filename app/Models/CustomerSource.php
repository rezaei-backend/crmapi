<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSource extends Model
{
    protected $table = 'customer_sources';

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
    public function usersDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UsersDetail::class, 'customer_sources_id', 'id');
    }
}
