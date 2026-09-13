# Pitfalls

The sharp edges, collected. Each one is pinned by a test, so if a line here stops being true the
build fails.

## Validation

* **`Delimited` drops empty items before counting.** `'a@b.com, ,c@d.com'` is two items, not
  three, so `min(3)` rejects it.
* **`Delimited` pluralises from the boundary.** A `min(2)` failure reads "at least 2 items" even
  when one item was supplied.
* **`Authorized` cannot tell you why it failed.** Not logged in, no such row, and gate denied all
  produce the same message — deliberately, so the response does not leak which ids exist.
* **`HasValidate::gainIntValue()` treats `0` as absent.** It falls back with `?:`, so `n=0` with a
  default of `7` yields `7`. `gainStringValue()` does the same for `''`.

## PostgreSQL arrays

* **`wherePgArrayContainsOnly()` matches empty arrays.** SQL's `ALL` is true for a zero-length
  array. Add `->whereRaw('array_length(col, 1) > 0')` if that is wrong for you.
* **`wherePgArrayContainsAny()` / `wherePgArrayContainsOnly()` compare one element.** A set raises
  `RuntimeException`; use `wherePgArrayOverlapWith()`.
* **`pgArrayAppend()` keeps duplicates** unless you pass `unique: true`, which in turn does not
  preserve the original order.
* **Array literals are unquoted.** `PostgresArray::encode(['a,b'])` produces `{a,b}`, which reads
  back as two elements. Use a `jsonb` column for values containing `,`, `{`, `}` or `"`.
* **Column names must be bare identifiers.** They cannot be bound as parameters, so each scope
  validates `name` or `table.name` and raises `InvalidArgumentException` otherwise.

## Sortable

* **`getDefaultSortingRestrictionsSql()` is raw SQL** interpolated into the `MAX()` subquery. Never
  build it from request input.
* **Override both restriction hooks or neither.** `getDefaultSortingRestrictionsSql()` bounds the
  stack; `forSortingRestrictions()` bounds the neighbour shifts. One without the other disagrees
  with itself.
* **No ordering is applied by default.** Add `SortOrderingAsc` or `SortOrderingDesc` yourself.
* **Reordering writes.** Inserting mid-stack issues an `UPDATE` across everything below.
* **Moving an existing row to the end leaves a gap.** The new position is `max(other rows) + 1`
  and nothing compacts the slot that was vacated, so four rows can end up at 2, 3, 4, 5. Positions
  are an ordering, not a dense index — read them with `sortingPositionOrderByAsc()`, never as
  array offsets.

## Model cache

* **A non-Redis store silently loses collection invalidation.** `DummyCacher` reports success and
  deletes nothing. Per-entity invalidation still works.
* **`RedisCacher` uses `KEYS`** — O(N) over the keyspace, and not cluster-safe.
* **`disableCache()` is static and process-wide.** Use `withoutCache()` so the flag is restored.
* **The cacher is memoised per class.** Call `flushResolvedCacher()` after swapping the store.

## Service providers

* **Register commands from `bootForConsole()`.** `ServiceProvider::commands()` defers through
  `Artisan::starting()`; anything registered after the console application is built never appears.
* **`packageSourcePath()` must point inside the package**, typically `__DIR__` from a file in
  `src/`. Every path helper is derived from its parent directory.
* **A missing config or route file throws.** `registerConfig()` and `registerRoutes()` name the
  file rather than passing `null` into the framework.

## Model traits

* **`AllowToExecute::checkPossibilityAndExecute()` calls `parent::`.** The guarded method has to
  override a real parent method.
* **`AllowToExecute`'s disallow map is static**, shared by every instance. The allow list is the
  per-instance escape hatch.
* **`RequestModelable` reads the unqualified key.** A dot in `$request->input()` means nested
  access, so the qualified `tags.id` would find nothing. Override `modelInputKeyName()` when the
  request field has another name.

## Resources

* **`PaginatedResourceArray` always wraps.** `links` and `meta` are "with" data, so even a
  resource with `$wrap = null` gets the default `data` key.

## Global helpers

* **`objectToArray()` returns `mixed`.** Scalars and objects implementing none of `Arrayable`,
  `JsonSerializable` or `Traversable` come back unchanged — a plain `stdClass` is not converted.
* **`toCollect(null)` is `Collection([null])`**, while `Collection::wrap(null)` is empty.
