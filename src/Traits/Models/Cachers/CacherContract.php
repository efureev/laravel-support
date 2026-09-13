<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models\Cachers;

/**
 * Strategy used by {@see \Php\Support\Laravel\Traits\Models\HasModelEntityCache} to build cache
 * keys and invalidate them.
 *
 * Implementations are constructed as `new Cacher(string $model, ?Store $store = null)`.
 */
interface CacherContract
{
    /**
     * Build the fully-qualified cache key for `$key`.
     *
     * `$prefix` defaults to the model's class basename.
     */
    public function prefixKey(?string $key = null, ?string $prefix = null): string;

    /**
     * Invalidate cached collections — `$key` defaults to the `list:*` pattern.
     *
     * @return bool whether anything was dropped
     */
    public function cacheForgetCollection(?string $key = null): bool;

    /**
     * Invalidate a single entity by its unprefixed key.
     */
    public function forgetByKey(string $key): bool;
}
