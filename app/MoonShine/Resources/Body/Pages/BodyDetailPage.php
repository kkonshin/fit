<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Body\Pages;

use App\MoonShine\Resources\Body\BodyResource;
use App\MoonShine\Resources\Image\ImageResource;
use Illuminate\Support\Carbon;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends DetailPage<BodyResource>
 */
class BodyDetailPage extends DetailPage
{
    public function getTitle(): string
    {
        return __('Просмотр параметров тела');
    }

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Preview::make('Дата', 'created_at')
                ->changePreview(fn ($date) => Carbon::parse($date)->isoFormat('D MMMM YYYY')),
            Number::make('Вес', 'weight'),
            Number::make('Пульс в покое', 'pulse'),
            Number::make('Шея', 'neck'),
            Number::make('Грудь', 'chest'),
            Number::make('Талия', 'waist'),
            Number::make('Живот', 'abdomen'),
            Number::make('Предплечье', 'bicep'),
            Number::make('Запястье', 'wrist'),
            Number::make('Бедра', 'hips'),
            Number::make('Бедро', 'hip'),
            Number::make('Голень', 'shin'),
            Number::make('Лодыжка', 'ankle'),
            HasMany::make('Фото', 'images', ImageResource::class)
                ->modalMode()
                ->fields([
                    Image::make('', 'path'),
                    Text::make('Комментарий', 'comment'),
                ]),
        ];
    }

    /**
     * @param  TableBuilder  $component
     * @return TableBuilder
     */
    protected function modifyDetailComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
