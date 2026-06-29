<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'food';

    protected $guarded = ['id'];

    protected $casts = [
        'proteins' => 'float',
        'fats' => 'float',
        'carbohydrates' => 'float',
    ];
}
