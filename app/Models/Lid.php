<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lid extends Model
{
    protected $table = 'lids';

    protected $fillable = [
        'phone',
        'fullname',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
