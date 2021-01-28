<?php
declare(strict_types=1);


namespace Php\Support\Laravel\Sorting\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SortOrderingAsc implements Scope
{
    /**
     * @param Builder $builder
     * @param Model|Sortable $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy($model::getSortingColumnName());
    }
}