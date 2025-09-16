<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAdd extends Model
{
    protected $table = 'orderadds';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'state',
        'type',
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
