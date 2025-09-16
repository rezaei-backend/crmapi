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

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function reservation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Reservation::class);
    }

    public function adds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderAdd::class);
    }

    public function visits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
