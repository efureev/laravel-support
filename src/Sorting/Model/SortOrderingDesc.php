<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Sorting\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use InvalidArgumentException;

/**
 * Global scope ordering a {@see Sortable} model by its sorting column, descending.
 *
 * ```php
 * protected static function booted(): void
 * {
 *     static::addGlobalScope(new SortOrderingDesc());
 * }
 * ```
 *
 * @see https://laravel.com/docs/13.x/eloquent#global-scopes
 *
 * @implements Scope<Model>
 */
class SortOrderingDesc implements Scope
{
    /**
     * @param Builder<covariant Model> $builder
     *
     * @throws InvalidArgumentException when the model does not use the Sortable trait
     */
    public function apply(Builder $builder, Model $model): void
    {
        // The Scope contract fixes this signature to any Model, so the sorting column has to be
        // checked at runtime. Failing loudly beats ordering by a column that does not exist.
        if (!method_exists($model, 'getSortingColumnName')) {
            throw new InvalidArgumentException(
                sprintf('%s requires the %s trait on %s.', static::class, Sortable::class, $model::class)
            );
        }

        $builder->orderByDesc($model->qualifyColumn($model->getSortingColumnName()));
    }
}
