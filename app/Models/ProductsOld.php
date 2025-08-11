<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsOld extends Model
{
    protected $table = 'products_old';

    protected $fillable = [
        'title',
        'en-title',
        'info',
        'cat',
        'buyprice',
        'osellprice',
        'sellprice',
        'tsellprice',
        'quantity',
        'tb',
        'state',
        'typehide',
    ];

    // No timestamps in this table
    public $timestamps = false;
}
