<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Body;

use App\Models\Body;
use App\MoonShine\Resources\Body\Pages\BodyDetailPage;
use App\MoonShine\Resources\Body\Pages\BodyFormPage;
use App\MoonShine\Resources\Body\Pages\BodyIndexPage;
use App\MoonShine\Resources\Concerns\RedirectAfterSaveToIndex;
use App\MoonShine\Resources\CachedModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends CachedModelResource<Body, BodyIndexPage, BodyFormPage, BodyDetailPage>
 */
class BodyResource extends CachedModelResource
{
    use RedirectAfterSaveToIndex;

    protected string $model = Body::class;

    protected int $itemsPerPage = 10;

    protected string $sortColumn = 'created_at';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            BodyIndexPage::class,
            BodyFormPage::class,
            BodyDetailPage::class,
        ];
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return __('Параметры тела');
    }
}
