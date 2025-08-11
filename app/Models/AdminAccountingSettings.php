<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAccountingSettings extends Model
{
    protected $table = 'adminaccountingsettings';

    protected $fillable = [
        'admin_id',
        'accounting_code',
        'expert_guid',
        'seller_guid',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
