<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Goal;

use App\Models\Goal;
use App\MoonShine\Resources\Concerns\RedirectAfterSaveToIndex;
use App\MoonShine\Resources\Goal\Pages\GoalDetailPage;
use App\MoonShine\Resources\Goal\Pages\GoalFormPage;
use App\MoonShine\Resources\Goal\Pages\GoalIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Goal, GoalIndexPage, GoalFormPage, GoalDetailPage>
 */
class GoalResource extends ModelResource
{
    use RedirectAfterSaveToIndex;

    protected string $model = Goal::class;

    protected string $title = 'Цели';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            GoalIndexPage::class,
            GoalFormPage::class,
            GoalDetailPage::class,
        ];
    }
}
