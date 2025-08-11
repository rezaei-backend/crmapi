<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallcenterCall extends Model
{
    protected $table = 'callcenter_calls';

    protected $fillable = [
        'visit_id',
        'admin_id',
        'information',
        'state',
        'statevo',
        'stateap',
        'follow_up',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
