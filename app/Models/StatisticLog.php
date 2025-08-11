<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatisticLog extends Model
{
    protected $table = 'statistic_logs';

    protected $fillable = [
        'admin_id',
        'stat_key_id',
        'start_date',
        'end_date',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
