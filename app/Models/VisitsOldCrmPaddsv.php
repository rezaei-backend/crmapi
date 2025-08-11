<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitsOldCrmPaddsv extends Model
{
    protected $table = 'visitsOLDCRMpaddsv';

    protected $fillable = [
        'prid',
        'tracking',
        'title',
        'q',
        'price',
        'prl',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // No timestamps in this table
    public $timestamps = false;
}
