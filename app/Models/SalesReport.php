<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReport extends Model
{
    protected $table = 'sales_reports';

    protected $fillable = [
        'full_name',
        'phone',
        'appointment_date',
        'amount',
        'deposit_date',
        'deposit_time',
        'tracking',
        'last_four_digits',
        'financial_approval',
        'financial_approval_date',
        'admin_id',
        'report_type',
        'status',
        'dbank',
        'description',
    ];

    protected $casts = [
        'financial_approval' => 'boolean',
        'status' => 'boolean',
        'appointment_date' => 'date',
        'deposit_date' => 'date',
        'financial_approval_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
