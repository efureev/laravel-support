<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models\Cachers;

interface CacherContract
{
    public function prefixKey(?string $key = null, ?string $prefix = null): string;

    public function cacheForgetCollection(?string $key = null): bool;

    public function forgetByKey(string $key): bool;
}
