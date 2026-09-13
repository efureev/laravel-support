<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Laravel\Exceptions\UnknownMethodException;

trait ModelQueryable
{
    use Modelable;

    /**
     * The model key the current object points at.
     *
     * Requires the using class to declare `modelKeyValueGainer(): callable` — a factory that
     * returns a `fn(string $keyName): mixed` reading the value from wherever it lives (request
     * input, route parameter, property, …).
     *
     * @throws UnknownMethodException when the using class does not declare `modelKeyValueGainer()`
     */
    public function modelKeyValue(): mixed
    {
        if (!method_exists($this, 'modelKeyValueGainer')) {
            throw new UnknownMethodException(static::class . '::modelKeyValueGainer');
        }

        $fn = $this->modelKeyValueGainer();

        // The gainer reads from an outside source (request input, route parameter), which knows
        // nothing about SQL table qualification — hand it the plain column name. The qualified
        // name is only ever used in the WHERE clause below.
        return $fn(static::modelInputKeyName());
    }


    /**
     * @return Builder<Model>
     */
    public function newQueryWithoutScopes(): Builder
    {
        return $this->model()->newQueryWithoutScopes();
    }

    /**
     * A query scoped to `$modelId`, or to the key the gainer reports when it is omitted.
     *
     * @param mixed $modelId one key, or a set of keys
     *
     * @return Builder<Model>
     */
    public function findModelQuery(mixed $modelId = null): Builder
    {
        $query = $this->newQueryWithoutScopes();
        $ids   = $modelId ?? $this->modelKeyValue();

        if (is_array($ids) || $ids instanceof Arrayable) {
            return $query->whereIn(static::modelKeyName(), $ids);
        }

        return $query->where(static::modelKeyName(), $ids);
    }

    /**
     * @param mixed $modelId one key, or a set of keys
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findModelOrFail(mixed $modelId = null): Model
    {
        if ($modelId) {
            return $this->findModelQuery($modelId)->firstOrFail();
        }

        return $this->findModelQuery()->firstOrFail();
    }
}
