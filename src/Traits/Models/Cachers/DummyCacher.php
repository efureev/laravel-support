<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models\Cachers;

use Illuminate\Contracts\Cache\Store;

/**
 * No-op cacher used when the active store cannot delete keys by pattern.
 *
 * Key prefixing still works, so {@see \Php\Support\Laravel\Traits\Models\HasModelEntityCache::remember()}
 * behaves normally; only pattern-based collection invalidation is a no-op.
 */
class DummyCacher implements CacherContract
{
    /**
     * `$store` is accepted and ignored so every cacher shares one constructor shape.
     *
     * @param class-string $model
     */
    public function __construct(private readonly string $model, protected readonly ?Store $store = null)
    {
    }

    public function prefixKey(?string $key = null, ?string $prefix = null): string
    {
        $prefix ??= class_basename($this->model);

        return "app:models:$prefix:$key";
    }

    public function cacheForgetCollection(?string $key = null): bool
    {
        return true;
    }

    public function forgetByKey(string $key): bool
    {
        return true;
    }
}
