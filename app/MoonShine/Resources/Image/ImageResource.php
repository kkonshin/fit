<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Image;

use App\Models\Image;
use App\MoonShine\Resources\CachedModelResource;
use App\MoonShine\Resources\Concerns\RedirectAfterSaveToIndex;
use App\MoonShine\Resources\Image\Pages\ImageDetailPage;
use App\MoonShine\Resources\Image\Pages\ImageFormPage;
use App\MoonShine\Resources\Image\Pages\ImageIndexPage;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends CachedModelResource<Image, ImageIndexPage, ImageFormPage, ImageDetailPage>
 */
class ImageResource extends CachedModelResource
{
    use RedirectAfterSaveToIndex;

    protected string $model = Image::class;

    protected int $itemsPerPage = 10;

    protected string $title = 'Фотографии';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ImageIndexPage::class,
            ImageFormPage::class,
            ImageDetailPage::class,
        ];
    }
}
