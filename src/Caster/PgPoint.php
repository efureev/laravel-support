<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Caster;

use Php\Support\Helpers\Arr;

class PgPoint extends PgArray
{
    public static function castToDatabase($value): ?string
    {
        return Arr::toPostgresPoint(static::normalize($value));
    }

    public function castFromDatabase(?string $value): array
    {
        return Arr::fromPostgresPoint($value);
    }
}
