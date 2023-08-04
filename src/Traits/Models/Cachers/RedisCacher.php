<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models\Cachers;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Config;

class RedisCacher implements CacherContract
{
    /**
     * @param \Illuminate\Cache\RedisStore $store
     */
    public function __construct(private readonly string $model, private readonly Store $store)
    {
    }

    public function prefixKey(string $key = null, string $prefix = null): string
    {
        $prefix ??= class_basename($this->model);

        return "app:models:$prefix:$key";
    }

    public function cacheForgetCollection(string $key = null): bool
    {
        return $this->removeByTemplate($this->prefixKey($key ?? 'list:*'));
    }

    private function removeByTemplate(string $template): bool
    {
        $client = $this->store->connection()->client();

        if (Config::get('database.redis.client') === 'predis') {
            $key = $this->store->getPrefix() . $template;
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

    public function forgetByKey(string $key): bool
    {
        return $this->store->forget($this->prefixKey($key));
    }
}
