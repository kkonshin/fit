<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Expense;

use App\Models\Expense;
use App\MoonShine\Resources\CachedModelResource;
use App\MoonShine\Resources\Concerns\RedirectAfterSaveToIndex;
use App\MoonShine\Resources\Expense\Pages\ExpenseDetailPage;
use App\MoonShine\Resources\Expense\Pages\ExpenseFormPage;
use App\MoonShine\Resources\Expense\Pages\ExpenseIndexPage;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends CachedModelResource<Expense, ExpenseIndexPage, ExpenseFormPage, ExpenseDetailPage>
 */
class ExpenseResource extends CachedModelResource
{
    use RedirectAfterSaveToIndex;

    protected string $model = Expense::class;

    protected int $itemsPerPage = 10;

    protected string $title = 'Платежи';

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
