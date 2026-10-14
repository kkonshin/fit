<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Food;

use App\Models\Food;
use App\MoonShine\Resources\Concerns\RedirectAfterSaveToIndex;
use App\MoonShine\Resources\Food\Pages\FoodDetailPage;
use App\MoonShine\Resources\Food\Pages\FoodFormPage;
use App\MoonShine\Resources\Food\Pages\FoodIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Food, FoodIndexPage, FoodFormPage, FoodDetailPage>
 */
class FoodResource extends ModelResource
{
    use RedirectAfterSaveToIndex;

    protected string $model = Food::class;

    protected string $title = 'Продукты';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            FoodIndexPage::class,
            FoodFormPage::class,
            FoodDetailPage::class,
        ];
    }
}
