<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatisticKey extends Model
{
    protected $table = 'statistic_keys';

    protected $fillable = [
        'key_name',
        'description',
        'disabled',
    ];

    protected $casts = [
        'disabled' => 'boolean',
    ];

    // No timestamps in this table
    public $timestamps = false;
}
