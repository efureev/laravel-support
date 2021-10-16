<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

use Illuminate\Database\Eloquent\Builder;
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
     */
    public function scopeWherePgArrayContains(Builder $query, string $column, mixed $value): Builder
    {
        if ($value instanceof \Closure) {
            $value = $value();
            if (!is_string($value)) {
                throw new \RuntimeException('Result`s value must have STRING type!');
            }
        } else {
            $value = Arr::toPostgresArray((array)$value);
        }

        return $query->whereRaw("$column @> ?", [$value]);
    }

    public function scopeWherePgArrayContainsAny(Builder $query, string $column, mixed $value): Builder
    {
        return $query->whereRaw("? = ANY ($column)", [$value]);
    }

    /**
     * Select rows which content only $value in $column
     *
     * @beware The result of ALL is “true” if all comparisons yield true,
     *  INCLUDING the case where the array has zero elements!
     *
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     *
     * @return Builder
     */
    public function scopeWherePgArrayContainsOnly(Builder $query, string $column, mixed $value): Builder
    {
        return $query->whereRaw("? = ALL ($column)", [$value]);
    }

    /**
     * Return rows which {$column}s contain value overlaps with $value
     *
     * @param Builder $query
     * @param string $column
     * @param array $value
     *
     * @return Builder
     */
    public function scopeWherePgArrayOverlapWith(Builder $query, string $column, array $value): Builder
    {
        return $query->whereRaw("$column && ?", [Arr::toPostgresArray($value)]);
    }
}
