<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitsOldCrm extends Model
{
    protected $table = 'visitsOLDCRM';

    protected $fillable = [
        'guid',
        'username',
        'tracking',
        'info',
        'channel',
        'uchannel',
        'reports',
        'advice',
        'price',
        'typeprice',
        'date',
        'time',
        'state',
        'complete',
        'typec',
        'vtype',
        'vst',
        'alert',
        'loginalert',
        'admin',
        'typevol',
        'working',
        'descriptionvo',
        'ersaln',
        'infoH',
        'off',
        'bedehin',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // No timestamps in this table
    public $timestamps = false;
}
