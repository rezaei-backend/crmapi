<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupCall extends Model
{
    protected $table = 'supcalls';

    protected $fillable = [
        'supmap_id',
        'status',
        'information',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
