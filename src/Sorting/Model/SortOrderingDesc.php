<?php

namespace Php\Support\Laravel\Sorting\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class SortOrderingDesc
 * @package Php\Support\Laravel\Sorting\Model
 */
class SortOrderingDesc implements Scope
{
    /**
     * @param Builder $builder
     * @param Model|Sortable $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderByDesc("{$model->getTable()}.{$model::getSortingColumnName()}");
    }
}
