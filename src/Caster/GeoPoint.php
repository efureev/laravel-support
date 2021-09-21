<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Caster;

use Php\Support\Types\GeoPoint as BaseGeoPoint;

/**
 * Class GeoPoint
 * @package Php\Support\Laravel\Caster
 *
 * GeoPoint for PG
 */
class GeoPoint extends BaseGeoPoint implements Caster
{
    public static function castToDatabase($value): ?string
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof static) {
            return $value->toPgDB();
        }

        throw new \RuntimeException('Invalid type of $value: ' . gettype($value));
    }

    public static function isEquivalent($value, $original): bool
    {
        return $value->toJson() === $original?->toJson();
    }

    public function value(): ?string
    {
        return static::castToDatabase($this);
    }
}
