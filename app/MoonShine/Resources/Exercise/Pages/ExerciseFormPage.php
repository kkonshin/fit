<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Exercise\Pages;

use App\MoonShine\Resources\Exercise\ExerciseResource;
use Illuminate\Support\Carbon;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends FormPage<ExerciseResource>
 */
class ExerciseFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                Preview::make('', 'created_at')
                    ->changePreview(fn ($date) => Carbon::parse($date)->isoFormat('D MMMM YYYY')),
                Text::make('Упражнение', 'name'),
                Number::make('Затраты калорий', 'calories'),
                Number::make('Продолжительность', 'duration'),
                Number::make('Средний пульс', 'pulse_avg'),
                Number::make('Максимальный пульс', 'pulse_max'),
                Json::make('Дополнительные сведения', 'extra')
                    ->fields([
                        Text::make('Расстояние', 'distance'),
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
