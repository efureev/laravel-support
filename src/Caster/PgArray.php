<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Caster;

use Php\Support\Helpers\Arr;

class PgArray implements Caster
{
    public static function castToDatabase($value): ?string
    {
        return Arr::toPostgresArray(static::normalize($value));
    }

    protected static function normalize($value): array
    {
        return array_filter($value);
    }

    public function castFromDatabase(?string $value): array
    {
        return Arr::fromPostgresArray($value);
    }

    public static function isEquivalent($value, $original): bool
    {
        return $value === $original;
    }
}
