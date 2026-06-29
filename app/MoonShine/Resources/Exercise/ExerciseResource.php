<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Exercise;

use App\Models\Exercise;
use App\MoonShine\Resources\Exercise\Pages\ExerciseDetailPage;
use App\MoonShine\Resources\Exercise\Pages\ExerciseFormPage;
use App\MoonShine\Resources\Exercise\Pages\ExerciseIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Exercise, ExerciseIndexPage, ExerciseFormPage, ExerciseDetailPage>
 */
class ExerciseResource extends ModelResource
{
    protected string $model = Exercise::class;

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
