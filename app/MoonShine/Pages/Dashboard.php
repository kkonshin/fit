<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Body;
use App\Models\Exercise;
use App\Models\Expense;
use App\MoonShine\Resources\Body\BodyResource;
use App\MoonShine\Resources\Exercise\ExerciseResource;
use MoonShine\Apexcharts\Components\LineChartMetric;
use MoonShine\Apexcharts\Support\SeriesItem;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Alert;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;

use function sprintf;

#[SkipMenu]
class Dashboard extends Page
{
    private const string COLOR_GREEN = '#22c55e';

    private const string COLOR_YELLOW = '#eab308';

    private const string COLOR_PINK = '#ec4899';

    private const string COLOR_RED = '#ef4444';

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
     *
     * @noinspection LaravelSimplifyOrderBy
     */
    protected function components(): iterable
    {
        $now = now();
        $sixMonthsAgo = $now->copy()->startOfMonth()->subMonths(5);

        $weights = Body::query()
            ->select(['weight', 'created_at'])
            ->whereNotNull('weight')
            ->whereBetween('created_at', [$sixMonthsAgo, $now])
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Body $body): string => $body->created_at->format('Y-m'))
            ->map(fn ($bodies) => $bodies->first())
            ->mapWithKeys(fn (Body $body): array => [
                $body->created_at->format('Y-m-d') => $body->weight,
            ])
            ->toArray();

        $lastPaidExpense = Expense::query()
            ->whereNotNull('paid_at')
            ->latest('paid_at')
            ->first();

        $quantityBought = $lastPaidExpense?->getRawOriginal('quantity_bought');
        $quantityUsed = $lastPaidExpense?->quantity_used ?? 0;
        $quantityRemaining = $quantityBought === null
            ? 0
            : max(0, (int) $quantityBought - $quantityUsed);
        $quantityRemainingMetric = ValueMetric::make('Осталось оплаченных занятий')
            ->value($quantityRemaining);

        if ($quantityRemaining === 1) {
            $quantityRemainingMetric->iconColor(Color::RED);
        }

        $bodyRecords = Body::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
        $currentBody = $bodyRecords->first();

        $caloriesYesterday = (int) Exercise::query()
            ->createdYesterday()
            ->get()
            ->sum('calories');

        $caloriesToday = (int) Exercise::query()
            ->createdToday()
            ->get()
            ->sum('calories');

        $calorieMetric = static function (string $label, int $value): ValueMetric {
            $color = $value >= 500
                ? self::COLOR_GREEN
                : ($value >= 250 ? self::COLOR_YELLOW : self::COLOR_PINK);

            return ValueMetric::make($label)
                ->value($value)
                ->progress(500)
                ->customAttributes([
                    'style' => sprintf(
                        '--color-primary: %s',
                        $color
                    ),
                ]);
        };

        $bodyMetric = static function (string $label, string $field, bool $showChange = true) use (
            $currentBody,
            $bodyRecords
        ): ValueMetric {
            $currentValue = $currentBody?->{$field};

            $previousValue = $bodyRecords
                ->skip(1)
                ->first(static fn (Body $body): bool => $body->{$field} !== null)?->{$field};

            $formatValue = static function (mixed $value): string {
                if ($value === null) {
                    return '—';
                }

                return rtrim(
                    rtrim(
                        number_format((float) $value,
                            2,
                            '.',
                            ''),
                        '0'
                    ), '.'
                );
            };

            $change = '';

            if ($showChange && is_numeric($currentValue) && is_numeric($previousValue)) {
                $difference = round((float) $currentValue - (float) $previousValue, 2);

                if ($difference !== 0.0) {
                    $color = $difference > 0 ? self::COLOR_GREEN : self::COLOR_RED;
                    $sign = $difference > 0 ? '+' : '-';
                    $change = sprintf(
                        '<span style="color:%s;font-size:50%%;">%s%s</span>',
                        $color,
                        $sign,
                        $formatValue(abs($difference))
                    );
                }
            }

            return ValueMetric::make($label)
                ->value($currentValue ?? '—')
                ->valueFormat(sprintf(
                    '<span style="display:flex;align-items:baseline;justify-content:space-between;gap:.5rem;width:100%%;">'
                    .'<span>%s</span>%s</span>',
                    $formatValue($currentValue),
                    $change
                ));
        };

        return [
            Box::make([
                Grid::make([
                    Column::make([
                        ActionButton::make(
                            'Создать упражнение',
                            app(ExerciseResource::class)->getFormPageUrl()
                        )->primary(),
                    ], 6),
                    Column::make([
                        ActionButton::make(
                            'Добавить параметры тела',
                            app(BodyResource::class)->getFormPageUrl()
                        )->primary(),
                    ], 6),
                ]),
            ])->class('mb-6'),
            Grid::make([
                Column::make([
                    Box::make([
                        $quantityRemainingMetric,
                        ...($quantityRemaining === 1
                            ? [Alert::make('bell-alert', Color::RED)
                                ->content('Требуется оплата!')]
                            : []),
                        $calorieMetric('Затраты калорий вчера', $caloriesYesterday),
                        $calorieMetric('Затраты калорий сегодня', $caloriesToday),
                    ]),

                ], 6),
                Column::make([
                    LineChartMetric::make('Вес')
                        ->series(SeriesItem::make('Вес', $weights)),
                ], 6),
            ]),
            Box::make('Текущие параметры тела', [
                Grid::make([
                    Column::make([
                        $bodyMetric('Вес', 'weight'),
                        $bodyMetric('Пульс в покое', 'pulse'),
                        $bodyMetric('Шея', 'neck'),
                        $bodyMetric('Плечи', 'shoulders'),
                    ], 3),
                    Column::make([
                        $bodyMetric('Грудь', 'chest'),
                        $bodyMetric('Талия', 'waist'),
                        $bodyMetric('Живот', 'abdomen'),
                        $bodyMetric('Бицепс', 'bicep'),
                    ], 3),
                    Column::make([
                        $bodyMetric('Запястье', 'wrist'),
                        $bodyMetric('Бедра', 'hips'),
                        $bodyMetric('Бедро', 'hip'),
                        $bodyMetric('Голень', 'shin'),
                    ], 3),
                    Column::make([
                        $bodyMetric('Лодыжка', 'ankle'),
                    ], 3),
                ]),
            ]),
        ];
    }
}
