<?php

namespace Php\Support\Laravel\Sorting\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Php\Support\Laravel\Sorting\Enum;

/**
 * Trait Sortable
 *
 * Use it in the Eloquent Model class to add sorting to it
 *
 */
trait Sortable
{
    /**
     * @var string Name of the global sorting scope
     */
    protected static $sortingScopeName = 'sortingPosition';

    /**
     * @var bool
     */
    protected static $sortingGlobalScope = true;


    /**
     * Call it in boot method of your Eloquent model
     *
     * @return void
     */
    protected static function defaultSortableBooting()
    {
        if (static::$sortingGlobalScope) {
            static::addGlobalScope(
                static::$sortingScopeName,
                function (Builder $builder) {
                    $builder->orderBy(Enum::SORTING_POSITION_COLUMN);
                }
            );
        }

        static::creating(
            function ($model) {
                $model->sorting_position = $model->sorting_position ?? 0;
            }
        );
    }
    
    /**
     * @return string
     */
    public static function getSortingScopeName(): string
    {
        return static::$sortingScopeName;
    }

    /**
     * @param $sortingPosition
     */
    public function setSortingPositionAttribute($sortingPosition)
    {
        $this->attributes[Enum::SORTING_POSITION_COLUMN] = $this->normalizeSortingPosition($sortingPosition);
    }

    /**
     * @param $sortingPosition
     * @return Expression
     */
    protected function normalizeSortingPosition($sortingPosition)
    {
        $oldSortingPosition = $this->getOriginal(Enum::SORTING_POSITION_COLUMN);
        if (empty($sortingPosition)) {
            $sortingPosition = $oldSortingPosition;

            if ($sortingPosition === null) {
                $sortingPosition = DB::raw($this->formDefaultSQL());
            }
        } elseif ($oldSortingPosition !== null) {
            $this->reorderBySortingPosition($oldSortingPosition, $sortingPosition);
        }

        return $sortingPosition;
    }

    /**
     * @return string
     */
    protected function formDefaultSQL(): string
    {
        $sortingPositionColumn = Enum::SORTING_POSITION_COLUMN;
        return <<<SQL
(SELECT
      CASE
        WHEN MAX({$sortingPositionColumn}) IS NOT NULL 
            THEN MAX({$sortingPositionColumn}) + 1
        ELSE 1
      END
FROM {$this->getTable()})
SQL;
    }

    /**
     * @param int $steps
     *
     * @return $this
     */
    public function upInSorting(int $steps)
    {
        $currentPosition = (int) $this->sorting_position;
        if ($steps > $currentPosition) {
            throw new \InvalidArgumentException('Current position is less than possible');
        }
        $this->sorting_position -= $steps;

        return $this;
    }

    /**
     * @param int $steps
     *
     * @return $this
     */
    public function downInSorting(int $steps)
    {
        if ($steps < 0) {
            throw new \InvalidArgumentException('Please, use upInSorting if you want to up Sortable in sorting');
        }

        if ($steps > 0) {
            $this->sorting_position += $steps;
        }

        return $this;
    }

    /**
     * @param int $oldPosition
     * @param int $newPosition
     *
     * @return void
     */
    protected function reorderBySortingPosition(int $oldPosition, int $newPosition): void
    {
        DB::transaction(
            function () use ($oldPosition, $newPosition) {
                Schema::table(
                    $this->getTable(),
                    function (Blueprint $blueprint) {
                        $blueprint->dropIndex("{$this->getTable()}_sorting_position_index");
                    }
                );
                if ($oldPosition > $newPosition) {
                    $this->incrementInReorder($oldPosition, $newPosition);
                } elseif ($oldPosition < $newPosition) {
                    $this->decrementInReorder($oldPosition, $newPosition);
                }
                Schema::table(
                    $this->getTable(),
                    function (Blueprint $blueprint) {
                        $blueprint->index('sorting_position');
                    }
                );
            }
        );
    }

    /**
     * @param int $oldPosition
     * @param int $newPosition
     */
    protected function incrementInReorder(int $oldPosition, int $newPosition): void
    {
        static::where(Enum::SORTING_POSITION_COLUMN, '>=', $newPosition)
            ->where(Enum::SORTING_POSITION_COLUMN, '<', $oldPosition)
            ->increment(Enum::SORTING_POSITION_COLUMN);
    }

    /**
     * @param int $oldPosition
     * @param int $newPosition
     */
    protected function decrementInReorder(int $oldPosition, int $newPosition): void
    {
        static::where(Enum::SORTING_POSITION_COLUMN, '<=', $newPosition)
            ->where(Enum::SORTING_POSITION_COLUMN, '>', $oldPosition)
            ->decrement(Enum::SORTING_POSITION_COLUMN);
    }
}
