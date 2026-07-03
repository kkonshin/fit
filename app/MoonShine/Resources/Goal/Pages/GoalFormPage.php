<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Goal\Pages;

use App\MoonShine\Resources\Goal\GoalResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends FormPage<GoalResource>
 */
class GoalFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Цель', 'name'),
                Json::make('Параметры', 'parameters')->fields([
                    Text::make('Вид активности', 'activity_type'),
                    Number::make('Расстояние, км', 'distance'),
                    Number::make('Средняя скорость, км/ч', 'average_speed'),
                    Number::make('Максимальная скорость, км/ч', 'max_speed'),
                    Number::make('Средний пульс', 'average_pulse'),
                    Number::make('Вес, кг', 'weight'),
                ]),
                Date::make('Достигнуть к', 'to_be_achieved_to'),
                Date::make('Достигнута', 'achieved_at'),
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
