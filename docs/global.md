# Global

Global helper functions are autoloaded via `src/Global/base.php`.

## toCollect

Returns a collection of a value.

```php
toCollect($model);   // Model        -> Collection([$model])
toCollect([$a, $b]); // array        -> Collection([$a, $b])
toCollect($coll);    // Collection   -> the same Collection
toCollect($value);   // mixed        -> Collection([$value])
```

## objectToArray

Recursively converts an object (or a structure of objects) into an array.

Supports `Illuminate\Contracts\Support\Arrayable`, `JsonSerializable` and `Traversable`.

```php
objectToArray($model);            // uses Arrayable::toArray()
objectToArray($jsonSerializable); // uses jsonSerialize()
objectToArray($iterator);         // uses iterator_to_array()
```
