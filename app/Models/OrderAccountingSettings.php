<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAccountingSettings extends Model
{
    protected $table = 'orderaccountingsettings';

    protected $fillable = [
        'order_id',
        'admin_accounting_id',
        'user_accounting_id',
        'accounting_guid',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
