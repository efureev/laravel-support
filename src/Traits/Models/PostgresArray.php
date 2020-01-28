<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

use Illuminate\Database\Eloquent\Builder;
use Php\Support\Helpers\Arr;

trait PostgresArray
{
    /**
     * Mutates php array to acceptable format of postgreSQL array field
     *
     * @param array $array
     *
     * @return string
     * @throws \Php\Support\Exceptions\JsonException
     */
    public static function mutateToPgArray(array $array): string
    {
        return Arr::toPostgresArray($array);
    }

    /**
     * Changes postgreSQL array field returned from PDO to php array
     *
     * @param string|null $value
     *
     * @return array
     */
    public static function accessPgArray(?string $value): array
    {
        return Arr::fromPostgresArray($value);
    }

    /**
     * Where database array $column has all of the elements in $value
     *
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     *
     * @return Builder
     * @throws \Php\Support\Exceptions\JsonException
     */
    public function scopeWherePgArrayContains(Builder $query, $column, $value): Builder
    {
        $value = self::mutateToPgArray((array)$value);

        return $query->whereRaw("$column @> ?", [$value]);
    }
}
