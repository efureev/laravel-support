<?php

namespace Php\Support\Laravel\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Sorting\Model\Sortable;

/**
 * @property string $model_type
 * @property string $model_id
 * @property string|null $title
 */
class SortEntityWithSortingRestrictions extends Model
{
    use Sortable;

    protected $fillable = [
        'title',
        'model_type',
        'model_id',
    ];

    protected $table = 'sort_entities_with_sorting_restrictions';
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * `'uuid'` is not a valid $keyType — Laravel 13 raises InvalidCastException for it.
     * The column is filled by Postgres `gen_random_uuid()` and read back via
     * `INSERT ... RETURNING id`, so `$incrementing` stays at its default.
     */
    protected $keyType = 'string';


    /**
     * @return string
     */
    protected function getDefaultSortingRestrictionsSql(): string
    {
        return "model_type = '{$this->model_type}' AND model_id = '{$this->model_id}' AND deleted_at IS NULL";
    }

    /**
     * @param Builder<static> $query
     *
     * @return Builder<static>
     */
    protected function forSortingRestrictions(Builder $query): Builder
    {
        return $query
            ->where('model_type', '=', $this->model_type)
            ->where('model_id', '=', $this->model_id)
            ->whereNull(['deleted_at']);
    }
}
