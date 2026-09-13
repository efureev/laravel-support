# Upgrading to 5.0

## Requirements

| | 4.x | 5.0 |
|---|---|---|
| PHP | `>= 8.4` | **`>= 8.5`** |
| Laravel | `illuminate/database: ^13.0` | `illuminate/*: ^13.0`, declared explicitly |

The package used to require only `illuminate/database` while actually using `Illuminate\Http`,
the facades, the cache, the validator and the container. 5.0 declares all of them, so Composer
can resolve them rather than relying on `laravel/framework` happening to be installed.

`ext-json` and `ext-mbstring` are now declared too.

## Breaking: `efureev/support` is gone

The dependency is dropped. Three exception classes moved into this package:

| 4.x | 5.0 |
|---|---|
| `Php\Support\Exceptions\InvalidParamException` | `Php\Support\Laravel\Exceptions\InvalidParamException` |
| `Php\Support\Exceptions\UnknownMethodException` | `Php\Support\Laravel\Exceptions\UnknownMethodException` |
| `Php\Support\Exceptions\MethodNotAllowedException` | `Php\Support\Laravel\Exceptions\MethodNotAllowedException` |

Each extends the same SPL class it did before, so a catch on the parent is unaffected:

```php
catch (\LogicException $e)          // still catches InvalidParamException
catch (\BadMethodCallException $e)  // still catches UnknownMethodException
catch (\RuntimeException $e)        // still catches MethodNotAllowedException
```

**What to do:** update any `use Php\Support\Exceptions\…` import to the new namespace. If your
application used `efureev/support` for its own reasons, require it directly.

The PostgreSQL array helpers it provided are now in
`Php\Support\Laravel\Helpers\PostgresArray` (`encode()`, `decode()`, `toIndexedArray()`).
Behaviour is unchanged except for one bug fix: the decoder treated a single quote as a quoting
character, so an element like `o'brien` was swallowed — `decode("{safe,o'brien}")` returned
`['safe']`. PostgreSQL's `array_out` quotes with double quotes only, so `'` is now an ordinary
character. A hand-written literal such as `{'x','y'}` consequently decodes to `["'x'", "'y'"]`
rather than `['x', 'y']`.

## Breaking: removed

| Removed | Why | Instead |
|---|---|---|
| `Php\Support\Laravel\Traits\Models\WrapQuery` | two lines, no users, no coverage | [`Builder::tap()`](https://laravel.com/docs/13.x/queries#debugging) or `when()` |
| `Php\Support\Laravel\Test\CreateHttpRequests` | a verbatim copy of the framework's private test plumbing | `Illuminate\Foundation\Testing\Concerns\MakesHttpRequests` |

## Behaviour changes

* **Translations publish to the right place.** The provider used to target
  `resource_path('lang/vendor/laravelSupport')`, which a Laravel 9+ application does not read.
  It now uses `$app->langPath()`, under the tag `laravel-support-lang`.
* **`Delimited`'s `:min` / `:max` are substituted.** They never were; the rule passed
  `minimum` / `maximum` while the language files said `:min` / `:max`, so users saw the raw
  placeholder. If you overrode these strings with `:minimum` / `:maximum` to work around it,
  switch back to `:min` / `:max`.
* **`Delimited` no longer counts blank items.** `'a, ,b'` is two items. A `min` that used to pass
  on padding now fails.
* **`AbstractRepository::findModel()` accepts integer keys.** It used to require a string and
  raise `InvalidParamException` for an `int`, which rejected the default Eloquent key type.
* **`ModelNotFoundException` keeps the id.** `findModel()` passed `null` where the id belonged,
  so the message lost it.
* **`RequestModelable` reads the unqualified key.** It asked `$request->input('table.column')`,
  where the dot means nested access — so it never resolved anything. Override
  `modelInputKeyName()` if your field name differs from the column.
* **`HasModelEntityCache` honours a configured resolver.** `cache.resolver.class` was invoked as
  a function rather than instantiated, so configuring one raised
  `Error: Call to undefined function`.
* **`RedisCacher::cacheForgetCollection()` works.** It passed Predis' argument order to phpredis
  (a `TypeError` on the default client), bound only the first key in its Lua script, and built a
  `KEYS` pattern without the cache prefix.
* **PostgreSQL array scopes validate the column name.** A name that is not a bare identifier now
  raises `InvalidArgumentException` instead of being interpolated into raw SQL.
* **`AllowToExecute::addMethodToAllowList()` returns `$this`.** It used to return `void`.

## Additions

* `Php\Support\Laravel\Casts\PostgresArrayCast` — the cast that makes the array scopes usable,
  previously only a test fixture.
* `PostgresArray::pgArrayAppend()` and `pgArrayRemove()` — the write half of the four read
  scopes, applied in one statement across every matching row.
* `Sortable::setLastForSortingPosition()` and the `sortingPositionBetween()` scope.
* `Delimited::maxItemLength()`, with the `laravelSupport::messages.delimited.item_length`
  translation.
* `HasValidate::gainFloatValue()` and `gainArrayValue()`.
* `HasModelEntityCache::enableCache()`, `withoutCache()`, `forget()`, `flushResolvedCacher()`.
* `AllowToExecute::removeMethodFromAllowList()`, `removeMethodFromDisallowMap()`,
  `isAllowToExecute()` is now public.
* `Modelable::modelInputKeyName()`.
* `HasPathHelpers::getDatabaseSeedersPath()` takes an optional argument, like its siblings.
