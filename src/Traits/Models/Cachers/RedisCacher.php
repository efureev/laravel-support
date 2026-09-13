<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models\Cachers;

use Illuminate\Cache\RedisStore;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Redis\Connections\PredisConnection;

/**
 * Cacher backed by a Redis store, able to drop whole key patterns in one round trip.
 *
 * @see https://laravel.com/docs/13.x/cache#redis
 */
class RedisCacher implements CacherContract
{
    /**
     * @param class-string $model
     */
    public function __construct(private readonly string $model, private readonly RedisStore $store)
    {
    }

    public function prefixKey(?string $key = null, ?string $prefix = null): string
    {
        $prefix ??= class_basename($this->model);

        return "app:models:$prefix:$key";
    }

    public function cacheForgetCollection(?string $key = null): bool
    {
        return $this->removeByTemplate($this->prefixKey($key ?? 'list:*'));
    }

    public function forgetByKey(string $key): bool
    {
        return $this->store->forget($this->prefixKey($key));
    }

    /**
     * Delete every key matching `$template`.
     *
     * @return bool whether at least one key was deleted
     */
    private function removeByTemplate(string $template): bool
    {
        // RedisStore prepends the cache prefix in PHP before the value ever reaches the client,
        // so the KEYS pattern has to carry it too. Whatever prefix the client itself is
        // configured with (phpredis OPT_PREFIX / predis key-prefix processor) is applied on top
        // by the client, for KEYS[1] as well.
        $pattern = $this->store->getPrefix() . $template;

        // `local k = unpack(t)` would bind only the FIRST element, so `del` would drop a single
        // key and still report success. Keep the table, then expand it into del's arguments.
        // KEYS returns fully-qualified names, and Lua runs server-side, so `del` needs no
        // further prefixing.
        $lua = <<<LUA
        local keys = redis.call('keys', KEYS[1])
        if #keys == 0 then
          return 0
        end

        return redis.call('del', unpack(keys))
        LUA;

        // Call `eval` on the connection, not on the raw client: phpredis takes
        // `eval(string $script, array $args, int $numKeys)` while predis takes
        // `eval($script, $numKeys, ...$args)`. Laravel's connection normalises the two.
        // @see \Illuminate\Redis\Connections\PhpRedisConnection::eval()
        // RedisStore::connection() is documented as the abstract Connection, whose `@mixin \Redis`
        // would resolve `eval` to phpredis's own argument order. Both concrete connections accept
        // the normalised (script, numKeys, ...args) form.
        /** @var PhpRedisConnection|PredisConnection $connection */
        $connection = $this->store->connection();

        $deleted = $connection->eval($lua, 1, $pattern);

        return (int)$deleted > 0;
    }
}
