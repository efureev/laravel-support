<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Helpers\PostgresArray;

/**
 * Casts a native PostgreSQL array column (`text[]`, `integer[]`, …) to and from a PHP array.
 *
 * Unlike Laravel's built-in `array` cast — which serializes to JSON and therefore needs a
 * `json`/`jsonb` column — this cast uses the PostgreSQL array literal format (`{a,b,c}`), so
 * the stored value stays a real array and the {@see \Php\Support\Laravel\Traits\Models\PostgresArray}
 * query scopes work against it.
 *
 * ```php
 * protected function casts(): array
 * {
 *     return ['tags' => PostgresArrayCast::class];
 * }
 * ```
 *
 * @see https://laravel.com/docs/13.x/eloquent-mutators#custom-casts
 *
 * @implements CastsAttributes<array<int, mixed>, array<array-key, mixed>>
 */
class PostgresArrayCast implements CastsAttributes
{
    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<int, mixed>|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        return PostgresArray::decode((string)$value);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return PostgresArray::encode((array)$value);
    }
}
