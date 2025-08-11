<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationOld extends Model
{
    protected $table = 'reservation_old';

    protected $fillable = [
        'date',
        'time',
        'name',
        'lname',
        'number',
        'admin',
        'prstater',
        'prtyper',
        'trackingr',
        'prre',
        'cdate',
        'ctime',
        'rnr',
        'state',
        'comstate',
        'flagsj',
        'infoH',
        'information',
    ];

    protected $casts = [
        'date' => 'date',
        'cdate' => 'date',
    ];

    // No timestamps in this table
    public $timestamps = false;
}
