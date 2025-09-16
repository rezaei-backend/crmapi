<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitDetail extends Model
{
    protected $table = 'visit_details';

    protected $fillable = [
        'visit_id',
        'problems_info',
        'abstinence_info',
        'doctor_info',
        'day_count',
        'score',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function visit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }
}
