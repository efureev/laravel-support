<?php

namespace Php\Support\Laravel\Sorting\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trait Sortable
 *
 * Use it in the Eloquent Model class to add sorting to it
 *
 */
trait Sortable
{
    /**
     * @var string Name of the column in sortingPosition scope
     */
    protected $sortingPositionColumn = 'sorting_position';

    /**
     * @var string Name of the global sorting scope
     */
    protected static $sortingScopeName = 'sortingPosition';

    /**
     * @var bool
     */
    protected static $sortingGlobalScope = true;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        if (static::$sortingGlobalScope) {
            static::addGlobalScope(
                static::$sortingScopeName,
                function (Builder $builder) {
                    $builder->orderBy((new static())->getSortingPositionColumn());
                }
            );
        }
    }

    /**
     * @return string
     */
    public function getSortingPositionColumn(): string
    {
        return $this->sortingPositionColumn;
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
        $oldSortingPosition = $this->getOriginal($this->sortingPositionColumn);
        if (empty($sortingPosition)) {
            $sortingPosition = $oldSortingPosition;

            if ($sortingPosition === null) {
                $sortingPosition = DB::raw($this->formDefaultSQL());
            }
        } elseif ($oldSortingPosition !== null) {
            $this->reorderBySortingPosition($oldSortingPosition, $sortingPosition);
        }

        $this->attributes[$this->sortingPositionColumn] = $sortingPosition;
    }

    protected function formDefaultSQL(): string
    {
        return <<<SQL
(SELECT
      CASE
        WHEN MAX({$this->sortingPositionColumn}) IS NOT NULL 
            THEN MAX({$this->sortingPositionColumn}) + 1
        ELSE 1
      END
FROM {$this->getTable()})
SQL;
    }

    /**
     * @param int $steps
     * @return $this
     */
    public function upInSorting(int $steps)
    {
        $currentPosition = $this->sortingPositionColumnValue();
        if ($steps > $currentPosition) {
            throw new \InvalidArgumentException('Current position is less than possible');
        }
        $this->{$this->sortingPositionColumn} -= $steps;

        return $this;
    }

    /**
     * @param int $steps
     * @return $this
     */
    public function downInSorting(int $steps)
    {
        if ($steps < 0) {
            throw new \InvalidArgumentException('Please, use upInSorting if you want to up Sortable in sorting');
        }
        $this->{$this->sortingPositionColumn} += $steps;

        return $this;
    }

    /**
     * @return int
     */
    public function sortingPositionColumnValue(): int
    {
        return (int) $this->{$this->sortingPositionColumn};
    }

    /**
     * @param int $oldPosition
     * @param int $newPosition
     * @return void
     */
    protected function reorderBySortingPosition(int $oldPosition, int $newPosition): void
    {
        DB::transaction(
            function () use ($oldPosition, $newPosition) {
                Schema::table(
                    $this->getTable(),
                    function (Blueprint $blueprint) {
                        $blueprint->dropIndex("{$this->getTable()}_{$this->sortingPositionColumn}_index");
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
                        $blueprint->index($this->sortingPositionColumn);
                    }
                );
            }
        );
    }

    protected function incrementInReorder(int $oldPosition, int $newPosition): void
    {
        static::where($this->sortingPositionColumn, '>=', $newPosition)
            ->where($this->sortingPositionColumn, '<', $oldPosition)
            ->increment($this->sortingPositionColumn);
    }

    protected function decrementInReorder(int $oldPosition, int $newPosition): void
    {
        static::where($this->sortingPositionColumn, '<=', $newPosition)
            ->where($this->sortingPositionColumn, '>', $oldPosition)
            ->decrement($this->sortingPositionColumn);
    }
}
