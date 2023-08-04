<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models\Cachers;

class DummyCacher implements CacherContract
{
    public function __construct(private readonly string $model)
    {
    }

    public function prefixKey(string $key = null, string $prefix = null): string
    {
        $prefix ??= class_basename($this->model);

        return "app:models:$prefix:$key";
    }

    public function cacheForgetCollection(string $key = null): bool
    {
        return true;
    }

    public function forgetByKey(string $key): bool
    {
        return true;
    }
}
