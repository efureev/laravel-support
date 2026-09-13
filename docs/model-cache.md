# Model cache

`Php\Support\Laravel\Traits\Models\HasModelEntityCache` caches per-model values and drops them
whenever the model is saved or deleted.

```php
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Traits\Models\HasModelEntityCache;

class Country extends Model
{
    use HasModelEntityCache;
}

Country::remember(static fn() => Country::query()->pluck('name', 'code')->all(), 'list:names');
```

Keys are namespaced as `app:models:<ClassBasename>:<key>`.

## Methods

| Method | Does |
|---|---|
| `remember(Closure $fn, string $key): mixed` | memoise `$fn` under `$key` for `cacheTtl()` seconds |
| `forget(string $key): bool` | drop what `remember()` stored — its counterpart |
| `cacheForgetByKey(string $key): bool` | drop one entity by its unprefixed key |
| `disableCache(): void` / `enableCache(): void` | turn caching off and back on, process-wide |
| `withoutCache(callable $fn): mixed` | run `$fn` uncached and restore the previous state, even on a throw |
| `flushResolvedCacher(): void` | forget the memoised cacher after swapping the store at runtime |

Protected seams: `cacheKeyName()` (the attribute used for entity keys, `id` by default),
`cacheTtl()` (seconds, one hour by default), `cacheForgetCollection()`.

While caching is disabled every read and write bypasses the store — `remember()` simply calls
its callback.

## Cachers

The cacher is chosen from the active [cache store](https://laravel.com/docs/13.x/cache):

| Store | Cacher | Collection invalidation |
|---|---|---|
| `RedisStore` | `Cachers\RedisCacher` | deletes every key matching `app:models:<Class>:list:*` |
| anything else | `Cachers\DummyCacher` | no-op |

Point `cache.resolver.class` at your own `Cachers\CacherContract` implementation to override the
choice. It is constructed as `new YourCacher(string $model, ?Store $store = null)`, and a class
that does not implement the contract raises `RuntimeException`.

```php
// config/cache.php
'resolver' => ['class' => App\Cache\TaggedCacher::class],
```

## Pitfalls

* **A non-Redis store silently loses collection invalidation.** `DummyCacher` reports success
  without deleting anything, because a store that cannot match key patterns has nothing to
  delete. Per-entity invalidation still works.
* **`RedisCacher` uses `KEYS`.** It is O(N) over the keyspace and is not safe against a Redis
  Cluster, where the pattern only reaches one node.
* **`disableCache()` is process-wide and static.** In tests, restore it — `withoutCache()` does
  that for you.
* **The cacher is memoised per class.** Swap the cache store at runtime and you must call
  `flushResolvedCacher()`.
