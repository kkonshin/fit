<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Expense;

use App\Models\Expense;
use App\MoonShine\Resources\Expense\Pages\ExpenseDetailPage;
use App\MoonShine\Resources\Expense\Pages\ExpenseFormPage;
use App\MoonShine\Resources\Expense\Pages\ExpenseIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Expense, ExpenseIndexPage, ExpenseFormPage, ExpenseDetailPage>
 */
class ExpenseResource extends ModelResource
{
    protected string $model = Expense::class;

    protected string $title = 'Расходы';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ExpenseIndexPage::class,
            ExpenseFormPage::class,
            ExpenseDetailPage::class,
        ];
    }
}
