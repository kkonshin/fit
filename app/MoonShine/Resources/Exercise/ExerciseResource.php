<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Exercise;

use App\Models\Exercise;
use App\MoonShine\Resources\CachedModelResource;
use App\MoonShine\Resources\Concerns\RedirectAfterSaveToIndex;
use App\MoonShine\Resources\Exercise\Pages\ExerciseDetailPage;
use App\MoonShine\Resources\Exercise\Pages\ExerciseFormPage;
use App\MoonShine\Resources\Exercise\Pages\ExerciseIndexPage;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends CachedModelResource<Exercise, ExerciseIndexPage, ExerciseFormPage, ExerciseDetailPage>
 */
class ExerciseResource extends CachedModelResource
{
    use RedirectAfterSaveToIndex;

    protected string $model = Exercise::class;

    protected int $itemsPerPage = 10;

    protected string $title = 'Упражнения';

    protected string $sortColumn = 'created_at';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ExerciseIndexPage::class,
            ExerciseFormPage::class,
            ExerciseDetailPage::class,
        ];
    }
}
