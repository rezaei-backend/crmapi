<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMap extends Model
{
    protected $table = 'supportmap';

    protected $fillable = [
        'user_id',
        'admin_id',
        'state',
        'enabled',
    ];

    // No timestamps in this table
    public $timestamps = false;
}
