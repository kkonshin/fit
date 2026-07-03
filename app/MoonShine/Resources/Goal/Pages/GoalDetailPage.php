<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Goal\Pages;

use App\MoonShine\Resources\Goal\GoalResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends DetailPage<GoalResource>
 */
class GoalDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
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
            Date::make('Создана', 'created_at'),
            Date::make('Обновлена', 'updated_at'),
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
