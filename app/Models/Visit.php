<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $table = 'visits';

    protected $fillable = [
        'order_id',
        'user_id',
        'information',
        'admin_id',
        'state',
        'visit_type',
        'working',
        'return_number',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function visitDetail(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(VisitDetail::class, 'visit_id', 'id');
    }

    public function calls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CallcenterCall::class, 'visit_id', 'id');
    }
}
