<?php


namespace Php\Support\Laravel\Sorting\Model;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(static::$sortingScopeName, function (Builder $builder) {
            $builder->orderBy((new static())->getSortingPositionColumn());
        });
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
                $sortingPosition = DB::raw(<<<SQL
(SELECT
      CASE
        WHEN MAX({$this->sortingPositionColumn}) IS NOT NULL 
            THEN MAX({$this->sortingPositionColumn}) + 1
        ELSE 1
      END
FROM {$this->getTable()})
SQL
                );
            }
        } elseif($oldSortingPosition !== null) {
            $this->reorderBySortingPosition($oldSortingPosition, $sortingPosition);
        }

        $this->attributes[$this->sortingPositionColumn] = $sortingPosition;
    }

    /**
     * @param int $oldPosition
     * @param int $newPosition
     * @return void
     */
    protected function reorderBySortingPosition(int $oldPosition, int $newPosition): void
    {
        Schema::table($this->getTable(), function (Blueprint $blueprint) {
            $blueprint->dropUnique("{$this->getTable()}_{$this->sortingPositionColumn}_unique");
        });
        if ($oldPosition > $newPosition) {
            static::where($this->sortingPositionColumn, '>=', $newPosition)->where($this->sortingPositionColumn, '<', $oldPosition)->increment($this->sortingPositionColumn);
        } elseif ($oldPosition < $newPosition) {
            static::where($this->sortingPositionColumn, '<=', $newPosition)->where($this->sortingPositionColumn, '>', $oldPosition)->decrement($this->sortingPositionColumn);
        }
//        Schema::table($this->getTable(), function (Blueprint $blueprint) {
//            $blueprint->unique($this->sortingPositionColumn);
//        });
    }
}
