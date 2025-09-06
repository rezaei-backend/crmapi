<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiseaseCategory extends Model
{
    use HasFactory;

    protected $table = 'diseases_category';

    protected $fillable = [
        'category_title',
    ];

    public function diseases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Disease::class, 'category_id');
    }
}
