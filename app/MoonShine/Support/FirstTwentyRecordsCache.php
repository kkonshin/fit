<?php

declare(strict_types=1);

namespace App\MoonShine\Support;

use Closure;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\LazyCollection;
use Psr\SimpleCache\InvalidArgumentException;

final class FirstTwentyRecordsCache
{
    private const string CacheVersion = 'v2';

    private const string RegistryPrefix = 'moonshine:first-twenty:v2:keys:';

    private function __construct() {}

    /**
     * @throws InvalidArgumentException
     */
    public static function remember(
        string $modelClass,
        string $key,
        Closure $callback,
    ): iterable|Collection|LazyCollection|CursorPaginator|Paginator {
        $cache = Cache::store();

        $fullKey = 'moonshine:first-twenty:'.self::CacheVersion.":$modelClass:$key";

        $result = $cache->get($fullKey);

        if (! is_iterable($result)) {
            $cache->forget($fullKey);
            $result = $callback();
            $cache->forever($fullKey, $result);
        }

        /** @var array<int, string> $keys */
        $keys = $cache->get(self::RegistryPrefix.$modelClass, []);

        if (! in_array($fullKey, $keys, true)) {
            $keys[] = $fullKey;
            $cache->forever(self::RegistryPrefix.$modelClass, $keys);
        }

        return $result;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function forgetForModel(string $modelClass): void
    {
        $cache = Cache::store();

        /** @var array<int, string> $keys */
        $keys = $cache->get(self::RegistryPrefix.$modelClass, []);

        foreach ($keys as $key) {
            $cache->forget($key);
        }

        $cache->forget(self::RegistryPrefix.$modelClass);
    }
}
