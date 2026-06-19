<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Food;

use Illuminate\Database\Eloquent\Model;
use App\Models\Food;
use App\MoonShine\Resources\Food\Pages\FoodIndexPage;
use App\MoonShine\Resources\Food\Pages\FoodFormPage;
use App\MoonShine\Resources\Food\Pages\FoodDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<Food, FoodIndexPage, FoodFormPage, FoodDetailPage>
 */
class FoodResource extends ModelResource
{
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
