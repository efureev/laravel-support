# PostgreSQL arrays

PostgreSQL array columns (`text[]`, `integer[]`, …) are not JSON, so Laravel's built-in `array`
cast — which serialises to JSON and needs a `json`/`jsonb` column — cannot read or write them.
This package supplies the missing pieces: a cast, four query scopes, and the literal
encoder/decoder underneath.

## Casts\PostgresArrayCast

`Php\Support\Laravel\Casts\PostgresArrayCast` converts a native array column to and from a PHP
array. It implements
[`CastsAttributes`](https://laravel.com/docs/13.x/eloquent-mutators#custom-casts).

```php
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Casts\PostgresArrayCast;
use Php\Support\Laravel\Traits\Models\PostgresArray;

class Post extends Model
{
    use PostgresArray;

    protected $casts = [
        'tags' => PostgresArrayCast::class,
    ];
}
```

The column itself has to be created with raw SQL — Laravel's schema builder has no native-array
column type:

```php
DB::statement('ALTER TABLE posts ADD COLUMN tags text[]');
```

A `null` column stays `null` in both directions.

## Traits\Models\PostgresArray

Four scopes over the
[PostgreSQL array operators](https://www.postgresql.org/docs/current/functions-array.html).

| Scope | SQL | Matches rows where |
|---|---|---|
| `wherePgArrayContains(string $column, mixed $value)` | `col @> ?` | the column contains **every** element of `$value` |
| `wherePgArrayContainsAny(string $column, mixed $value)` | `? = ANY (col)` | the column contains the single element `$value` |
| `wherePgArrayContainsOnly(string $column, mixed $value)` | `? = ALL (col)` | every element of the column equals `$value` |
| `wherePgArrayOverlapWith(string $column, array $value)` | `col && ?` | the column shares at least one element with `$value` |

```php
Post::wherePgArrayContains('tags', ['php', 'laravel'])->get();  // has both
Post::wherePgArrayContainsAny('tags', 'php')->get();            // has php
Post::wherePgArrayOverlapWith('tags', ['php', 'python'])->get(); // has either
```

`wherePgArrayContains()` also accepts a `Closure` returning a ready-made array literal, for cases
where you want to build it yourself:

```php
Post::wherePgArrayContains('tags', static fn(): string => '{php,laravel}')->get();
```

The closure must return a string; anything else raises `RuntimeException`.

### Writing

Two scopes change an array column in place, in one statement, across every row the query matches.

| Scope | SQL | Does |
|---|---|---|
| `pgArrayAppend(string $column, array $values, bool $unique = false)` | `array_cat` | append `$values`; `unique: true` collapses duplicates |
| `pgArrayRemove(string $column, array $values)` | `array_remove` | drop every occurrence of each value |

Both return the number of rows updated, and both are no-ops returning `0` for an empty
`$values`. A `null` column is treated as empty, so appending to it starts the array off.

```php
Post::query()->whereKey($id)->pgArrayAppend('tags', ['php']);
Post::wherePgArrayContains('tags', ['php'])->pgArrayAppend('tags', ['web']);
Post::query()->whereKey($id)->pgArrayRemove('tags', ['php', 'web']);
```

Duplicates are kept unless you ask otherwise — a PostgreSQL array is an ordered list, not a set.
`unique: true` collapses them through `array_agg(DISTINCT …)`, which does not preserve the
original order.

`Builder::update()` inlines raw expressions and carries no bindings for them, so the array
literal is quoted by the PDO driver rather than interpolated. Values containing apostrophes are
safe.

### Column names are validated, not escaped away

A column name cannot be bound as a query parameter, so each scope checks it against a plain
identifier pattern (`name` or `table.name`) and then quotes it through the connection's grammar.
A name that is not a bare identifier raises `InvalidArgumentException` — this is what stops
`'tags) OR (1=1'` from becoming a SQL injection.

### Pitfalls

* **`wherePgArrayContainsOnly()` matches empty arrays.** SQL's `ALL` is true for a zero-length
  array, so rows with `{}` come back too. Add
  `->whereRaw('array_length(tags, 1) > 0')` when that is not what you want.
* **`wherePgArrayContainsAny()` and `wherePgArrayContainsOnly()` compare one element.** Passing a
  set raises `RuntimeException`; use `wherePgArrayOverlapWith()` for that.

## Helpers\PostgresArray

`Php\Support\Laravel\Helpers\PostgresArray` is the literal encoder used by the cast and the
scopes. Useful directly when you build raw queries.

| Method | Does |
|---|---|
| `encode(array $array): string` | PHP array → `{a,b,c}` literal; nested arrays nest, keys are dropped |
| `decode(?string $literal, int $start = 0, ?int &$end = null): array` | literal → PHP array; digit-only items become `int`, other numerics `float` |
| `toIndexedArray(array $array): array` | drop string keys recursively |

```php
use Php\Support\Laravel\Helpers\PostgresArray;

PostgresArray::encode(['php', 'laravel']); // '{php,laravel}'
PostgresArray::encode([[1, 2], [3]]);      // '{{1,2},{3}}'
PostgresArray::decode('{1,2.5}');          // [1, 2.5]
PostgresArray::decode("{o'brien}");        // ["o'brien"]
PostgresArray::decode(null);               // []
```

`decode()` follows PostgreSQL's own output format, where elements are quoted with **double**
quotes only. An apostrophe is an ordinary character, so a hand-written `{'x','y'}` decodes to
`["'x'", "'y'"]` — two elements that happen to contain quotes.

### Pitfall

Elements are written **unquoted**, so a value containing `,`, `{`, `}` or `"` does not survive a
round trip: `encode(['a,b'])` produces `{a,b}`, which decodes back to two elements. Use a
`jsonb` column for values that may contain those characters.
