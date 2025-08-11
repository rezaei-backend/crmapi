<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccountingSettings extends Model
{
    protected $table = 'useraccountingsettings';

    protected $fillable = [
        'user_id',
        'accounting_code',
        'accounting_guid',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
