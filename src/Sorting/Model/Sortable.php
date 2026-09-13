<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Sorting\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;

/**
 * Keeps an Eloquent model in a hand-ordered stack (drag-and-drop friendly).
 *
 * A row saved with a position of `null`, `0` or less lands at the end of the stack; a row saved
 * with a position already taken pushes the rest down; moving an existing row shifts only the
 * rows between its old and new place. Override
 * {@see self::getDefaultSortingRestrictionsSql()} and {@see self::forSortingRestrictions()} to
 * keep several independent stacks in one table.
 *
 * ```php
 * class Slide extends Model
 * {
 *     use Sortable;
 * }
 *
 * $slide->setSortingPosition(2)->save();
 * Slide::sortingPositionOrderByAsc()->get();
 * ```
 *
 * @see \Php\Support\Laravel\Sorting\Database\Sortable::columnSortingPosition()
 *
 * @method static Builder<static> sortingPositionGreaterThen(int $value, bool $andSelf = true)
 * @method static Builder<static> sortingPositionLessThen(int $value, bool $andSelf = true)
 * @method static Builder<static> sortingPositionBetween(int $from, int $to)
 * @method static Builder<static> sortingPositionOrderByDesc()
 * @method static Builder<static> sortingPositionOrderByAsc()
 *
 * @mixin Model
 */
trait Sortable
{
    /** Column holding the position; override per model to rename it. */
    protected static ?string $sortingColumnName = null;

    /**
     * Call it in boot method of your Eloquent model
     *
     * @return void
     */
    protected static function bootSortable(): void
    {
        static::saving(
            static function (self $model): void {
                $model->onSavingSortingPosition();
            }
        );

        static::saved(
            static function (self $model): void {
                $model->onSavedSortingPosition();
            }
        );
        /*
            static::addGlobalScope(new SortOrderingDesc);
            // OR
            static::addGlobalScope(
                static::getSortingScopeName(),
                fn(Builder $builder) => static::sortingOrderingFn($builder)
            );
        */
    }

    /**
     * @param Builder<static> $builder
     *
     * @return Builder<static>
     */
    protected static function sortingOrderingFn(Builder $builder): Builder
    {
        if ($direction = static::sortingOrderingDirection()) {
            $builder->orderBy(static::getSortingColumnName(), $direction);
        }

        return $builder;
    }

    /**
     * @return 'asc'|'desc'|null null disables the automatic ordering
     */
    protected static function sortingOrderingDirection(): ?string
    {
        return 'desc';
    }

    public static function getSortingScopeName(): string
    {
        return 'sortingPosition';
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

    /**
     * Queue a move to the head of the stack. Everything else shifts down by one on save.
     */
    public function setFirstForSortingPosition(): self
    {
        return $this->setSortingPosition(1);
    }

    /**
     * Queue a move to the end of the stack — the counterpart of
     * {@see self::setFirstForSortingPosition()}.
     *
     * A non-positive position already means "end of the stack", so this is the readable spelling
     * of that rule rather than new behaviour.
     */
    public function setLastForSortingPosition(): self
    {
        return $this->setSortingPosition(0);
    }

    public function onSavingSortingPosition(): void
    {
        $this->normalizeSortingPosition();
        $this->reorderingSortingPosition();
    }

    public function onSavedSortingPosition(): void
    {
        $this->refreshSortingPosition();
    }

    public function refreshSortingPosition(): void
    {
        if ($this->{static::getSortingColumnName()} instanceof Expression) {
            $this->{static::getSortingColumnName()} = $this->setKeysForSelectQuery(
                $this->newQueryWithoutScopes()
            )
                ->firstOrFail([static::getSortingColumnName()])
                ->{static::getSortingColumnName()};
        }
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

    private function incrementInReorder(int $new, int $old): void
    {
        $column = static::getSortingColumnName();
        $query  = $this->forSortingRestrictions($this->newQuery())
            ->where($column, '>=', $new);

        if ($this->exists) {
            $query->where($column, '<', $old);
        }

        $query->increment($column);
    }

    private function decrementInReorder(int $new, int $old): void
    {
        $column = static::getSortingColumnName();
        $query  = $this->forSortingRestrictions($this->newQuery())
            ->where($column, '<=', $new);

        if ($this->exists) {
            $query->where($column, '>', $old);
        }

        $query->decrement($column);
    }

    protected function getDefaultSortingRestrictionsSql(): string
    {
        return '';
    }

    /**
     * @param Builder<static> $query
     *
     * @return Builder<static>
     */
    public function scopeSortingPositionGreaterThen(Builder $query, int $value, bool $andSelf = true): Builder
    {
        return $query->where(static::getSortingColumnName(), $andSelf ? '>=' : '>', $value);
    }

    /**
     * @param Builder<static> $query
     *
     * @return Builder<static>
     */
    public function scopeSortingPositionLessThen(Builder $query, int $value, bool $andSelf = true): Builder
    {
        return $query->where(static::getSortingColumnName(), $andSelf ? '<=' : '<', $value);
    }

    /**
     * Rows whose position lies between `$from` and `$to`, inclusive. The bounds may arrive in
     * either order.
     *
     * @param Builder<static> $query
     *
     * @return Builder<static>
     */
    public function scopeSortingPositionBetween(Builder $query, int $from, int $to): Builder
    {
        return $query->whereBetween(
            static::getSortingColumnName(),
            $from <= $to ? [
                $from,
                $to,
            ] : [
                $to,
                $from,
            ]
        );
    }

    /**
     * @param Builder<static> $query
     *
     * @return Builder<static>
     */
    public function scopeSortingPositionOrderByDesc(Builder $query): Builder
    {
        return $query->orderByDesc(static::getSortingColumnName());
    }

    /**
     * @param Builder<static> $query
     *
     * @return Builder<static>
     */
    public function scopeSortingPositionOrderByAsc(Builder $query): Builder
    {
        return $query->orderBy(static::getSortingColumnName());
    }

    /**
     * @param Builder<static> $query
     *
     * @return Builder<static>
     */
    protected function forSortingRestrictions(Builder $query): Builder
    {
        return $query;
    }
}
