<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'parameters' => 'array',
        'achieved_at' => 'date',
        'to_be_achieved_to' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
