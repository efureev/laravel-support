<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

if (!function_exists('toCollect')) {
    /**
     * Wrap any value in a Collection.
     *
     * Almost `Collection::wrap()`, with two deliberate differences:
     *
     * | input        | `toCollect()`        | `Collection::wrap()`      |
     * |--------------|----------------------|---------------------------|
     * | `null`       | `Collection([null])` | `Collection([])`          |
     * | a Collection | the same instance    | a new, equal instance     |
     *
     * Everything else — scalars, arrays, objects, Eloquent models — behaves identically.
     * Reach for `Collection::wrap()` unless you need one of the two rows above.
     *
     * ```php
     * toCollect($model);       // Collection([$model])
     * toCollect([$a, $b]);     // Collection([$a, $b])
     * toCollect($collection);  // the very same Collection object
     * toCollect(null);         // Collection([null])  <- wrap() would give Collection([])
     * ```
     *
     * @see https://laravel.com/docs/13.x/collections#method-collection-wrap
     *
     * @return Collection<array-key, mixed>
     */
    function toCollect(mixed $model): Collection
    {
        if ($model instanceof Collection) {
            return $model;
        }

        if (is_array($model)) {
            return collect($model);
        }

        return collect([$model]);
    }
}

if (!function_exists('objectToArray')) {
    /**
     * Recursively convert objects into arrays.
     *
     * Unwraps `Arrayable`, then `JsonSerializable`, then `Traversable` — in that order — and
     * recurses into the result. Scalars and objects implementing none of the three are returned
     * untouched, so the result is not guaranteed to be array-only.
     *
     * ```php
     * objectToArray($model);            // Arrayable::toArray()
     * objectToArray($jsonSerializable); // jsonSerialize()
     * objectToArray($generator);        // iterator_to_array()
     * objectToArray(new stdClass());    // returned as-is: stdClass is none of the three
     * ```
     */
    function objectToArray(mixed $data): mixed
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        } elseif ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        }

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as &$item) {
            $item = objectToArray($item);
        }

        return $data;
    }
}
