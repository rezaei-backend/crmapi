<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersDetail extends Model
{
    protected $table = 'usersdetail';

    protected $fillable = [
        'user_id',
        'city',
        'town',
        'address',
        'zip_code',
        'reign',
        'information',
        'customer_sources_id',
        'description',
        'day_count',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customerSource(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CustomerSource::class, 'customer_sources_id', 'id');
    }
}
