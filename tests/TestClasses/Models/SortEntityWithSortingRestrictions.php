<?php


namespace Php\Support\Laravel\Tests\TestClasses\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Sorting\Model\Sortable;

class SortEntityWithSortingRestrictions extends Model
{
    use Sortable;

    protected $table = 'sort_entities_with_sorting_restrictions';
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $keyType = 'uuid';

   

    /**
     * @return string
     */
    protected function getDefaultSortingRestrictionsSql(): string
    {
        return "model_type = '{$this->model_type}' AND model_id = '{$this->model_id}'";
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    protected function forSortingRestrictions(Builder $query): Builder
    {
        return $query
            ->where('model_type', '=', $this->model_type)
            ->where('model_id', '=', $this->model_id);
    }
}
