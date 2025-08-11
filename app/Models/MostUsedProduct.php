<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MostUsedProduct extends Model
{
    protected $table = 'most_used_products';

    protected $fillable = [
        'profile',
        'product_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
