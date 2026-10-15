<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\MoonShine\Support\FirstTwentyRecordsCache;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Ability;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

abstract class CachedModelResource extends ModelResource
{
    /**
     * @param Ability $ability
     * @return bool
     */
    protected function isCan(Ability $ability): bool
    {
        $user = MoonShineAuth::getGuard()->user();

        if ($user instanceof MoonshineUser && ! $user->isSuperUser()) {
            return in_array($ability, [Ability::VIEW_ANY, Ability::VIEW], true);
        }

        return parent::isCan($ability);
    }

    /**
     * @return iterable|Collection|LazyCollection|CursorPaginator|Paginator
     * @throws Throwable
     * @throws InvalidArgumentException
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
