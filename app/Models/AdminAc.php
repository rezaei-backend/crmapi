<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAc extends Model
{
    protected $table = 'admin_ac';

    protected $fillable = [
        'admin_id',
        'feature_id',
        'can_create',
        'can_edit',
        'can_enable_disable',
    ];

    protected $casts = [
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_enable_disable' => 'boolean',
    ];
}
