<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * @mixin Model
 */
trait HasModelEntityCache
{
    public static function bootHasModelEntityCache(): void
    {
        static::registerEventsForCache();
    }

    protected static function registerEventsForCache(): void
    {
        static::created(static fn(Model $model) => static::cacheForgetCollection());
        static::saved($fn = static::cacheForgetFn());
        static::updated($fn);
        static::deleted($fn);
    }

    protected static function cacheForgetFn(): callable
    {
        return static function (Model $model) {
            static::cacheForget($model);
            static::cacheForgetCollection();
        };
    }

    protected static function cacheForget(Model $model): bool
    {
        return Cache::forget(
            static::cachePrefixKey($model->{static::cacheKeyName()})
        );
    }

    protected static function cacheForgetCollection(string $key = 'list:*'): bool
    {
        return static::removeByTemplate(static::cachePrefixKey($key));
    }

    private static function removeByTemplate(string $template)
    {
        /** @var \Illuminate\Cache\RedisStore $store */
        $store  = Cache::getStore();
        $client = $store->connection()->client();

        if (Config::get('database.redis.client') === 'predis') {
            $key = $store->getPrefix() . $template;
        } else {
            $key = $client->_prefix($template);
        }

        $lua = <<<LUA
        local keys = unpack(redis.call('keys', KEYS[1]))
        if not keys then
          return 0
        end
        
        return redis.call('del', keys)
        LUA;

        $result = $client->eval(
            $lua,
            1,
            $key
        );

        return $result > 0;
    }

    protected static function cacheKeyName(): string
    {
        return 'id';
    }


    protected static function cachePrefixKey(string $key = null, string $prefix = null): string
    {
        $prefix ??= class_basename(static::class);

        return "app:models:$prefix:$key";
    }

    protected static function cacheTtl(): int
    {
        return 60 * 60;
    }

    public static function remember(callable $fn, string $key): mixed
    {
        return Cache::remember(
            static::cachePrefixKey($key),
            static::cacheTtl(),
            $fn
        );
    }
}
