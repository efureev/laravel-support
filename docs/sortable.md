# Sortable

Keeps model rows in a hand-ordered stack — the shape drag-and-drop reordering needs.

## Migration helper

`Php\Support\Laravel\Sorting\Database\Sortable::columnSortingPosition()` adds the position
column: an indexed `unsignedInteger` defaulting to `0`.

```
columnSortingPosition(Blueprint $table, string $columnName = 'sorting_position'): ColumnDefinition
```

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Php\Support\Laravel\Sorting\Database\Sortable;

return new class extends Migration {
    use Sortable;

    public function up(): void
    {
        Schema::create('slides', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title')->nullable();
            static::columnSortingPosition($table);
        });
    }
};
```

## Model trait

`Php\Support\Laravel\Sorting\Model\Sortable` does the bookkeeping.

```php
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Sorting\Model\Sortable;

class Slide extends Model
{
    use Sortable;
}
```

### Ordering rules

* A row saved with a position of `null`, `0` or below lands at the **end** of the stack —
  `max + 1`.
* A row saved into a position that is already taken pushes everything from there down by one.
* A row saved above the current maximum keeps the position it asked for; nothing else moves.
* Moving an existing row shifts only the rows between its old and its new place. The maximum
  position does not change.

### Methods

| Method | Does |
|---|---|
| `setSortingPosition(int $value): self` | queue a move; `<= 0` means "end of the stack" |
| `setFirstForSortingPosition(): self` | move to the head of the stack |
| `setLastForSortingPosition(): self` | move to the end of the stack |
| `sortingPosition(): int` | the current position |
| `getSortingColumnName(): string` | the column in use — `sorting_position` unless overridden |
| `getSortingScopeName(): string` | the name used when registering a named global scope |

Nothing is written until you `save()`; the trait hooks `saving` and `saved`.

### Query scopes

| Scope | Does |
|---|---|
| `sortingPositionGreaterThen(int $value, bool $andSelf = true)` | rows at or after `$value` |
| `sortingPositionLessThen(int $value, bool $andSelf = true)` | rows at or before `$value` |
| `sortingPositionBetween(int $from, int $to)` | rows between the two bounds, inclusive, in either order |
| `sortingPositionOrderByAsc()` | order ascending |
| `sortingPositionOrderByDesc()` | order descending |

```php
Slide::sortingPositionOrderByAsc()->get();
Slide::sortingPositionGreaterThen(3, andSelf: false)->get();
```

### Renaming the column

```php
class Slide extends Model
{
    use Sortable;

    protected static ?string $sortingColumnName = 'position';
}
```

Pass the same name to `columnSortingPosition()` in the migration.

### Ordering by default

Register one of the global scopes. See
[global scopes](https://laravel.com/docs/13.x/eloquent#global-scopes).

```php
use Php\Support\Laravel\Sorting\Model\SortOrderingAsc;

protected static function booted(): void
{
    static::addGlobalScope(new SortOrderingAsc());
}
```

`SortOrderingDesc` is the mirror image. Both raise `InvalidArgumentException` when applied to a
model that does not use the `Sortable` trait — ordering by a column that does not exist would
otherwise fail as an opaque SQL error.

### Independent stacks in one table

Override both restriction hooks. `getDefaultSortingRestrictionsSql()` narrows the `MAX()` lookup
that decides the end of the stack; `forSortingRestrictions()` narrows the queries that shift
neighbours. Override one without the other and the two halves disagree.

```php
protected function getDefaultSortingRestrictionsSql(): string
{
    return "album_id = '{$this->album_id}' AND deleted_at IS NULL";
}

protected function forSortingRestrictions(Builder $query): Builder
{
    return $query->where('album_id', $this->album_id)->whereNull('deleted_at');
}
```

## Pitfalls

* **`getDefaultSortingRestrictionsSql()` is raw SQL.** It is interpolated into the `MAX()`
  subquery as-is. Only build it from values you control — never from request input.
* **Reordering costs writes.** Inserting into the middle of a stack of *n* rows issues an
  `UPDATE` across the rows below it.
* **Moving an existing row to the end leaves a gap.** The new position is `max(other rows) + 1`
  and the vacated slot is not compacted. Positions are an ordering, not a dense index.
* **`bootSortable()` registers no global scope.** Ordering is opt-in; add `SortOrderingAsc` or
  `SortOrderingDesc` yourself.
