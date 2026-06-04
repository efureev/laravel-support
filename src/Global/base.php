<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

if (!function_exists('toCollect')) {
    /**
     * @param mixed $model
     *
     * @return Collection
     */
    function toCollect(mixed $model): Collection
    {
        if ($model instanceof Model) {
            return collect([$model]);
        }

        if (is_array($model)) {
            return collect($model);
        }

        if ($model instanceof Collection) {
            return $model;
        }

        return collect([$model]);
    }
}


if (!function_exists('objectToArray')) {
    /**
     * @param mixed $data
     *
     * @return mixed
     */
    function objectToArray(mixed $data): mixed
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof \JsonSerializable) {
            $data = $data->jsonSerialize();
        } elseif ($data instanceof \Traversable) {
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
