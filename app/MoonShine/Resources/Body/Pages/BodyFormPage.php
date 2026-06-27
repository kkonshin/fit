<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Body\Pages;

use App\Models\Body;
use App\MoonShine\Resources\Body\BodyResource;
use App\MoonShine\Resources\Image\ImageResource;
use Illuminate\Support\Carbon;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Fields\Relationships\RelationRepeater;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends FormPage<BodyResource>
 */
class BodyFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        $previousWeight = Body::query()->select('weight')
            ->orderBy('updated_at', 'desc')
            ->first()
            ->weight;

        return [
            Grid::make([
                Column::make([
                    Preview::make('', 'created_at')
                        ->changePreview(fn ($date) => Carbon::parse($date)->isoFormat('D MMMM YYYY')),
                    Box::make('Параметры:', [
                        ID::make(),
                        Number::make('Вес', 'weight')
                            ->default($previousWeight)
                            ->step(0.05)
                            ->buttons(),
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
                        // TODO сохранять оригинальные названия, или присваивать в форме + поле с комментарием?
                        RelationRepeater::make(
                            'Изображения',
                            'images',
                            resource: ImageResource::class
                        )
                            ->fields([
                                ID::make(),
                                Image::make('Фотография', 'path')
                                    ->dir('upload/images')
                                    ->disk('public'),
                                Text::make('Комментарий', 'comment'),
                            ])
                            ->removable(),
                    ]),
                ]),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [];
    }

    /**
     * @param  FormBuilder  $component
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
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
