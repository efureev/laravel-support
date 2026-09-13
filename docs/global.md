# Global helpers

Two functions, autoloaded through `src/Global/base.php`. Both are guarded by
`function_exists()`, so an application that already defines them wins.

## toCollect

```
toCollect(mixed $model): Illuminate\Support\Collection
```

Wraps any value in a Collection. Almost
[`Collection::wrap()`](https://laravel.com/docs/13.x/collections#method-collection-wrap), with
two deliberate differences:

| input | `toCollect()` | `Collection::wrap()` |
|---|---|---|
| `null` | `Collection([null])` | `Collection([])` |
| a Collection | the same instance | a new, equal instance |

Everything else — scalars, arrays, objects, Eloquent models — behaves identically.

```php
toCollect($model);      // Collection([$model])
toCollect([$a, $b]);    // Collection([$a, $b])
toCollect($collection); // the very same object
toCollect(null);        // Collection([null])
```

Reach for `Collection::wrap()` unless you need one of the two rows above.

## objectToArray

```
objectToArray(mixed $data): mixed
```

Recursively turns objects into arrays. Unwraps
[`Arrayable`](https://laravel.com/docs/13.x/contracts#specification), then `JsonSerializable`,
then `Traversable` — in that order — and recurses into the result.

```php
objectToArray($model);            // Arrayable::toArray()
objectToArray($jsonSerializable); // jsonSerialize()
objectToArray($generator);        // iterator_to_array()
```

### Pitfall

The return type is `mixed`, not `array`. A scalar comes back unchanged, and so does an object
that implements none of the three interfaces — a plain `stdClass` is returned as-is rather than
being converted. Cast to `(array)` first if you need that.
