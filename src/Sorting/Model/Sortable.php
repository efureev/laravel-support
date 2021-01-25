<?php

namespace Php\Support\Laravel\Sorting\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;

/**
 * Trait Sortable
 *
 * Use it in the Eloquent Model class to add sorting to it
 *
 * @method Sortable sortingPositionGreaterThen(int $value, bool $andSelf = true)
 * @method Sortable sortingPositionLessThen(int $value, bool $andSelf = true)
 *
 * @mixin Model
 * @mixin Builder
 */
trait Sortable
{
    /**
     * Call it in boot method of your Eloquent model
     *
     * @return void
     */
    protected static function bootSortable(): void
    {
        static::saving(
            static function (Model $model) {
                $model->onSavingSortingPosition();
            }
        );
    }

    public static function getSortingColumnName(): string
    {
        return static::$sortingColumnName ?? 'sorting_position';
    }

    public function setSortingPosition(int $value): self
    {
        if ($value <= 0) {
            $value = new Expression($this->sqlForMaxQuery());
        }
        $this->attributes[static::getSortingColumnName()] = $value;

        return $this;
    }

    private function normalizeSortingPosition(): self
    {
        $position = $this->{static::getSortingColumnName()} ?? 0;
        if ($position instanceof Expression) {
            return $this;
        }

        return $this->setSortingPosition($position);
    }

    public function sortingPosition(): int
    {
        return $this->{static::getSortingColumnName()} ?? 0;
    }

    public function setFirstForSortingPosition(): self
    {
        return $this->setSortingPosition(1);
    }

    public function onSavingSortingPosition()
    {
        $this->normalizeSortingPosition();
        $this->reorderingSortingPosition();
    }


    protected function sqlForMaxQuery(): string
    {
        $where = [];
        if ($this->exists) {
            $id     = $this->getKey();
            $idName = $this->getKeyName();
            switch ($this->keyType) {
                case 'int':
                case 'integer':
                    break;
                default:
                    $id = "'$id'";
            }

            $where[] = "($idName <> $id)";
        }

        if ($defaultSortingRestrictions = $this->getDefaultSortingRestrictionsSql()) {
            $where[] = $defaultSortingRestrictions;
        }
        if ($where) {
            $where = 'WHERE ' . implode(' AND ', $where);
        } else {
            $where = '';
        }
        $sortingColumnName = static::getSortingColumnName();
        return <<<SQL
(
    WITH max_s_p AS (select MAX({$sortingColumnName}) as m FROM {$this->getTable()} {$where})

    SELECT CASE
        WHEN m IS NOT NULL THEN m + 1 ELSE 1 END as v
    FROM max_s_p
    )
SQL;
    }

    private function reorderingSortingPosition(): void
    {
        if (($position = $this->{static::getSortingColumnName()}) instanceof Expression) {
            return;
        }

        if ($position > 0) {
            $column = static::getSortingColumnName();
            $new    = $this->sortingPosition();
            $old    = $this->getRawOriginal($column);

            if ($old === null) {
                $this->incrementInReorder($new, $new);
            } else {
                if ($new > $old) {
                    $this->decrementInReorder($new, $old);
                } else {
                    $this->incrementInReorder($new, $old);
                }
            }
        }
    }

    private function incrementInReorder($new, $old): void
    {
        $column = static::getSortingColumnName();
        $query  = $this->where($column, '>=', $new);

        if ($this->exists) {
            $query->where($column, '<', $old);
        }

        $query->increment($column);
    }

    private function decrementInReorder($new, $old): void
    {
        $column = static::getSortingColumnName();
        $query  = $this->where($column, '<=', $new);

        if ($this->exists) {
            $query->where($column, '>', $old);
        }

        $query->decrement($column);
    }

    protected function getDefaultSortingRestrictionsSql(): string
    {
        return '';
    }

    public function scopeSortingPositionGreaterThen(Builder $query, int $value, bool $andSelf = true)
    {
        return $query->where(static::getSortingColumnName(), $andSelf ? '>=' : '>', $value);
    }

    public function scopeSortingPositionLessThen(Builder $query, int $value, bool $andSelf = true)
    {
        return $query->where(static::getSortingColumnName(), $andSelf ? '<=' : '<', $value);
    }
}
