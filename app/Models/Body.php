<?php

namespace App\Models;

use Database\Factories\BodyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Body extends Model
{
    /** @use HasFactory<BodyFactory> */
    use HasFactory;

    protected $table = 'body';

    protected $guarded = ['id'];

    protected $casts = [
        'weight' => 'float',
    ];

    /**
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
