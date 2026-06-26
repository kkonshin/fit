<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Body;
use App\Models\Exercise;
use MoonShine\Apexcharts\Components\LineChartMetric;
use MoonShine\Apexcharts\Support\SeriesItem;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;

#[SkipMenu]
class Dashboard extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Результаты';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        $weights = Body::query()
            ->select(['weight', 'created_at'])
            ->get()
            ->mapWithKeys(function ($weight) {
                return [
                    $weight->created_at->format('Y-m-d') => $weight->weight,
                ];
            })
            ->toArray();

        return [
            Grid::make([
                Column::make([
                    Box::make([
                        ValueMetric::make('Затраты калорий вчера')
                            ->value(static fn (): int => Exercise::createdYesterday()->get()
                                ->sum('calories'))
                            ->progress(500),
                        ValueMetric::make('Затраты калорий сегодня')
                            ->value(static fn (): int => Exercise::createdToday()->get()
                                ->sum('calories'))
                            ->progress(500),
                    ]),

                ], 6),
                Column::make([
                    LineChartMetric::make('Вес')
                        ->series(SeriesItem::make('Вес', $weights)),
                ], 6),
            ]),
        ];
    }
}
