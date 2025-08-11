<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminsDash extends Model
{
    protected $table = 'admins_dash';

    protected $fillable = [
        'admin_id',
        'feature_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
