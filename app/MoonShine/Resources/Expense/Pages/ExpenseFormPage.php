<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Expense\Pages;

use App\MoonShine\Fields\FloatNumber;
use App\MoonShine\Resources\Expense\ExpenseResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends FormPage<ExpenseResource>
 */
class ExpenseFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Расход', 'name'),
                FloatNumber::make('Сумма', 'amount'),
                Number::make('Кол-во оплаченных', 'quantity_bought'),
                Number::make('Кол-во использованных', 'quantity_used')->readonly(),
                Date::make('Оплатить к', 'due_date'),
                Date::make('Оплачено', 'paid_at'),
            ]),
        ];
    }

    /**
     * @return array[]
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'quantity_bought' => ['nullable', 'integer', 'min:0'],
            'quantity_used' => ['nullable', 'integer', 'min:0'],
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
