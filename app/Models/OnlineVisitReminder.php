<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineVisitReminder extends Model
{
    protected $table = 'online_visit_reminder';

    protected $fillable = [
        'visit_id',
        'admin_id',
        'date',
        'time',
        'state',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
