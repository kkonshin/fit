<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['calories' => 'integer'];

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeCreatedToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeCreatedYesterday(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->subDay()->startOfDay(),
            now()->subDay()->endOfDay(),
        ]);
    }
}
