<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

use Closure;
use Illuminate\Cache\RedisStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Php\Support\Laravel\Traits\Models\Cachers\CacherContract;
use Php\Support\Laravel\Traits\Models\Cachers\DummyCacher;
use Php\Support\Laravel\Traits\Models\Cachers\RedisCacher;
use RuntimeException;

/**
 * Caches model entities and invalidates them on `saved` / `deleted`.
 *
 * The cacher is picked from the active cache store: a Redis store gets {@see RedisCacher}
 * (which can drop whole key patterns), anything else gets {@see DummyCacher} (per-key
 * invalidation only — collection keys are left alone). Point `cache.resolver.class` at your
 * own {@see CacherContract} implementation to override the choice.
 *
 * @see https://laravel.com/docs/13.x/cache
 *
 * @mixin Model
 */
trait HasModelEntityCache
{
    public static bool $cacheEnable = true;

    /** @var array<class-string, CacherContract> */
    protected static array $cacheStores = [];

    /**
     * Stop reading and writing the cache for this model, process-wide.
     */
    public static function disableCache(): void
    {
        static::$cacheEnable = false;
    }

    /**
     * Resume caching for this model. Counterpart of {@see self::disableCache()}.
     */
    public static function enableCache(): void
    {
        static::$cacheEnable = true;
    }

    /**
     * Run `$callback` with the cache switched off, then restore the previous state.
     */
    public static function withoutCache(callable $callback): mixed
    {
        $previous            = static::$cacheEnable;
        static::$cacheEnable = false;

        try {
            return $callback();
        } finally {
            static::$cacheEnable = $previous;
        }
    }

    public static function bootHasModelEntityCache(): void
    {
        static::registerEventsForCache();
    }

    protected static function resolveStoreDriver(): CacherContract
    {
        return static::$cacheStores[static::class] ??= static::resolveStoreDriverCls();
    }

    /**
     * Forget the memoized cacher — call it after swapping the cache store at runtime.
     */
    public static function flushResolvedCacher(): void
    {
        unset(static::$cacheStores[static::class]);
    }

    /**
     * The cacher configured via `cache.resolver.class`, or null when none is configured.
     *
     * @throws RuntimeException when the configured class does not implement CacherContract
     */
    protected static function getEntityCacheResolver(): ?CacherContract
    {
        $cacheResolverCls = Config::get('cache.resolver.class');

        if (!is_string($cacheResolverCls) || !class_exists($cacheResolverCls)) {
            return null;
        }

        if (!is_subclass_of($cacheResolverCls, CacherContract::class)) {
            throw new RuntimeException(
                sprintf('cache.resolver.class must implement %s, got %s', CacherContract::class, $cacheResolverCls)
            );
        }

        return new $cacheResolverCls(static::class, Cache::getStore());
    }

    protected static function resolveStoreDriverCls(): CacherContract
    {
        if ($resolver = static::getEntityCacheResolver()) {
            return $resolver;
        }

        return match (Cache::getStore()::class) {
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
        return static function (Model $model): void {
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

        return static::cacheForgetByKey((string)$model->{static::cacheKeyName()});
    }

    public static function cacheForgetByKey(string $key): bool
    {
        if (!static::$cacheEnable) {
            return true;
        }

        return static::resolveStoreDriver()->forgetByKey($key);
    }

    protected static function cacheForgetCollection(?string $key = null): bool
    {
        if (!static::$cacheEnable) {
            return true;
        }

        return static::resolveStoreDriver()->cacheForgetCollection($key);
    }

    /**
     * The attribute used to build a per-entity cache key.
     */
    protected static function cacheKeyName(): string
    {
        return 'id';
    }

    protected static function cachePrefixKey(?string $key = null, ?string $prefix = null): string
    {
        return static::resolveStoreDriver()->prefixKey($key, $prefix);
    }

    /**
     * Cache lifetime in seconds. One hour by default.
     */
    protected static function cacheTtl(): int
    {
        return 60 * 60;
    }

    /**
     * Memoize `$fn` under `$key` for {@see self::cacheTtl()} seconds.
     *
     * Calls `$fn` directly — without touching the cache — while caching is disabled.
     *
     * @see https://laravel.com/docs/13.x/cache#retrieve-store
     *
     * @template TValue
     *
     * @param Closure(): TValue $fn
     *
     * @return TValue
     */
    public static function remember(Closure $fn, string $key): mixed
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

    /**
     * Drop what {@see self::remember()} stored under `$key`. Counterpart of `remember()`.
     */
    public static function forget(string $key): bool
    {
        if (!static::$cacheEnable) {
            return true;
        }

        return Cache::forget(static::cachePrefixKey($key));
    }
}
