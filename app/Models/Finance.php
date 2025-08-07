<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    protected $table = 'finance';

    protected $fillable = [
        'full_name',
        'phone',
        'record_turn',
        'unit',
        'deposit_date',
        'deposit_time',
        'amount',
        'refund_reason',
        'card_number',
        'last_four_digits',
        'financial_approval',
        'refund_approved',
        'admin_id',
        'turn_type',
        'turn_date',
        'turn_time',
        'status',
        'dbank',
        'description',
    ];

    protected $casts = [
        'financial_approval' => 'boolean',
        'refund_approved' => 'boolean',
        'status' => 'boolean',
        'deposit_date' => 'date',
        'turn_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
