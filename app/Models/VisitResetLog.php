<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitResetLog extends Model
{
    protected $table = 'visit_reset_logs';

    protected $fillable = [
        'visit_id',
        'admin_id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'logged_at' => 'datetime',
    ];
}
