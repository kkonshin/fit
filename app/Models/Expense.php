<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Expense extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * @noinspection PhpUnused
     * @noinspection LaravelSimplifyOrderBy
     */
    public function getQuantityUsedAttribute(mixed $value): ?int
    {
        $paidAt = $this->getRawOriginal('paid_at');

        if ($paidAt === null) {
            return $value === null ? null : (int) $value;
        }

        $nextPaidAt = self::query()
            ->whereNotNull('paid_at')
            ->where('paid_at', '>', $paidAt)
            ->orderBy('paid_at')
            ->value('paid_at');

        $used = Exercise::query()
            ->where('name', 'Зал')
            ->whereDate('created_at', '>=', Carbon::parse($paidAt))
            ->when(
                $nextPaidAt === null,
                fn ($query) => $query->whereDate('created_at', '<=', today()),
                fn ($query) => $query->whereDate('created_at', '<', Carbon::parse($nextPaidAt)),
            )
            ->count();

        $quantityBought = $this->getRawOriginal('quantity_bought');

        return $quantityBought === null
            ? $used
            : min($used, (int) $quantityBought);
    }
}
