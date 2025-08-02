<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    public $timestamps = false;

    protected $fillable = [
        'title', 'qtitle', 'quantity', 'price', 'information', 'discount', 'discount_price', 'product_unit', 'enabled'
    ];
}
