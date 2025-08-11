<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LidManagerLog extends Model
{
    protected $table = 'lidmanagerlog';

    protected $fillable = [
        'admin_id',
        'to_admin_id',
        'quantity',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
