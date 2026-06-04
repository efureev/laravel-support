<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Helpers\Arr;

/**
 * Casts a native PostgreSQL array column (e.g. `text[]`, `integer[]`) to/from a PHP array.
 *
 * Unlike the built-in `array` cast (which serializes to JSON), this cast uses the
 * PostgreSQL array literal format (`{a,b,c}`) so the value is compatible with real
 * array columns and the `PostgresArray` query scopes.
 *
 * @implements CastsAttributes<array, array>
 */
class PostgresArrayCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        return Arr::fromPostgresArray((string)$value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return Arr::toPostgresArray((array)$value);
    }
}
