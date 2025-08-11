<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersOld extends Model
{
    protected $table = 'users_old';

    protected $fillable = [
        'hesabdari',
        'buyercode',
        'guid',
        'usersex',
        'fname',
        'lname',
        'info',
        'phone',
        'cid',
        'birthday',
        'ostan',
        'shahr',
        'address',
        'codp',
        'channel',
        'uchannel',
        'pass',
        'pneeded',
        'business',
        'complete',
        'state',
        'cdate',
        'ctime',
        'admin',
        'bpasokh',
        'reign',
    ];

    protected $casts = [
        'birthday' => 'date',
        'cdate' => 'date',
    ];

    // No timestamps in this table
    public $timestamps = false;
}
