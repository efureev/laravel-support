<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

use Illuminate\Database\Eloquent\Builder;
use Php\Support\Exceptions\JsonException;
use Php\Support\Helpers\Arr;

trait PostgresArray
{
    /**
     * Where database array $column has all of the elements in $value
     *
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     *
     * @return Builder
     * @throws JsonException
     */
    public function scopeWherePgArrayContains(Builder $query, $column, $value): Builder
    {
        $value = Arr::toPostgresArray((array)$value);

        return $query->whereRaw("$column @> ?", [$value]);
    }
}
