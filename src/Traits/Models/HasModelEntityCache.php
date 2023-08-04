<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

use Illuminate\Cache\RedisStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Php\Support\Laravel\Traits\Models\Cachers\CacherContract;
use Php\Support\Laravel\Traits\Models\Cachers\DummyCacher;
use Php\Support\Laravel\Traits\Models\Cachers\RedisCacher;

/**
 * @mixin Model
 */
trait HasModelEntityCache
{
    public static bool $cacheEnable = true;

    public static function disableCache(): void
    {
        static::$cacheEnable = false;
    }

    public static function bootHasModelEntityCache(): void
    {
        static::registerEventsForCache();
    }

    protected static array $cacheStores = [];

    protected static function resolveStoreDriver(): CacherContract
    {
        return static::$cacheStores[static::class] ??= static::resolveStoreDriverCls();
    }

    protected static function getEntityCacheResolver(): ?CacherContract
    {
        $cacheResolverCls = (string)Config::get('cache.resolver.class');
        if (class_exists($cacheResolverCls)) {
            return $cacheResolverCls(static::class, Cache::getStore());
        }

        return null;
    }

    protected static function resolveStoreDriverCls(): CacherContract
    {
        if ($cacheResolverCls = static::getEntityCacheResolver()) {
            return $cacheResolverCls;
        }

        return $cacheResolver ?? match (Cache::getStore()::class) {
            RedisStore::class => new RedisCacher(static::class, Cache::getStore()),
            default => new DummyCacher(static::class),
        };
    }

    protected static function registerEventsForCache(): void
    {
        static::saved($fn = static::cacheForgetFn());
        static::deleted($fn);
    }

    protected static function cacheForgetFn(): callable
    {
        return static function (Model $model) {
            if (!static::$cacheEnable) {
                return;
            }

            static::cacheForget($model);
            static::cacheForgetCollection();
        };
    }

    protected static function cacheForget(Model $model): bool
    {
        if (!static::$cacheEnable) {
            return true;
        }

        return static::cacheForgetByKey($model->{static::cacheKeyName()});
    }

    public static function cacheForgetByKey(string $key): bool
    {
        if (!static::$cacheEnable) {
            return true;
        }

        return static::resolveStoreDriver()->forgetByKey($key);
    }

    protected static function cacheForgetCollection(string $key = null): bool
    {
        return static::resolveStoreDriver()->cacheForgetCollection($key);
    }

    protected static function cacheKeyName(): string
    {
        return 'id';
    }

    protected static function cachePrefixKey(string $key = null, string $prefix = null): string
    {
        return static::resolveStoreDriver()->prefixKey($key, $prefix);
    }

    protected static function cacheTtl(): int
    {
        return 60 * 60;
    }

    public static function remember(callable $fn, string $key): mixed
    {
        if (!static::$cacheEnable) {
            return $fn();
        }

        return Cache::remember(
            static::cachePrefixKey($key),
            static::cacheTtl(),
            $fn
        );
    }
}
