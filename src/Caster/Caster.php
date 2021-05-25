<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Caster;

interface Caster
{
    public static function castToDatabase($value): ?string;

    public function castFromDatabase(?string $value);

    public static function isEquivalent($value, $original): bool;

    public function value(): mixed;
}
