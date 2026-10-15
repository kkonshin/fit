<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\MoonShine\Support\FirstTwentyRecordsCache;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use MoonShine\Laravel\Resources\ModelResource;
use Throwable;

abstract class CachedModelResource extends ModelResource
{
    /**
     * @throws Throwable
     */
    public function getItems(): iterable|Collection|LazyCollection|CursorPaginator|Paginator
    {
        $page = (int) $this->getQueryParam('page', 1);

        if ($page > 2) {
            return parent::getItems();
        }

        $params = $this->getQueryParams()->toArray();
        $key = md5(serialize($params));
        $modelClass = $this->getModel()::class;

        return FirstTwentyRecordsCache::remember(
            $modelClass,
            $key,
            fn () => parent::getItems(),
        );
    }
}
