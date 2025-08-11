<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'tracking',
        'user_id',
        'information',
        'order_type',
        'state',
        'cart',
        'pos',
        'cash',
        'discount_code',
        'discount_percentage',
        'admin_id',
        'sent_at',
        'discount',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
