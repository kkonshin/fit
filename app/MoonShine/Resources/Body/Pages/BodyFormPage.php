<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Body\Pages;

use App\Models\Body;
use App\MoonShine\Fields\FloatNumber;
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
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
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
        $previousBody = Body::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        return [
            Grid::make([
                Column::make([
                    Date::make('', 'created_at')
                        ->format('d-m-Y')
                        ->default(Carbon::today()->toDateTimeString()),
                    Box::make('Параметры:', [
                        ID::make(),
                        FloatNumber::make('Вес', 'weight')
                            ->default($previousBody?->weight)
                            ->step(0.05)
                            ->buttons(),
                        Number::make('Пульс в покое', 'pulse')
                            ->default($previousBody?->pulse),
                        Number::make('Шея', 'neck')
                            ->default($previousBody?->neck),
                        Number::make('Грудь', 'chest')
                            ->default($previousBody?->chest),
                        Number::make('Талия', 'waist')
                            ->default($previousBody?->waist),
                        Number::make('Живот', 'abdomen')
                            ->default($previousBody?->abdomen),
                        Number::make('Предплечье', 'bicep')
                            ->default($previousBody?->bicep),
                        Number::make('Запястье', 'wrist')
                            ->default($previousBody?->wrist),
                        Number::make('Бедра', 'hips')
                            ->default($previousBody?->hips),
                        Number::make('Бедро', 'hip')
                            ->default($previousBody?->hip),
                        Number::make('Голень', 'shin')
                            ->default($previousBody?->shin),
                        Number::make('Лодыжка', 'ankle')
                            ->default($previousBody?->ankle),
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

    /**
     * @return array[]
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [
            'created_at' => ['required', 'date'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:250'],
            'pulse' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'neck' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'chest' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'waist' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'abdomen' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'bicep' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'wrist' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'hips' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'hip' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'shin' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'ankle' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'images' => ['nullable', 'array'],
            'images.*.path' => ['nullable', 'file', 'image', 'max:10240'],
        ];
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
