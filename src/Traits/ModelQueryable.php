<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Php\Support\Exceptions\UnknownMethodException;

trait ModelQueryable
{
    use Modelable;


    //    abstract protected function modelKeyValueGainer(): callable;

    /**
     * @return mixed
     */
    public function modelKeyValue()
    {
        if (!method_exists($this, 'modelKeyValueGainer')) {
            $class = get_class($this);
            throw new UnknownMethodException("Missing method {$class}'::modelKeyValueGainer'");
        }

        $fn = $this->modelKeyValueGainer();

        return $fn(static::modelKeyName());
    }


    public function newQueryWithoutScopes()
    {
        return $this->model()->newQueryWithoutScopes();
    }

    /**
     * @param mixed|null $modelId
     *
     * @return Builder
     */
    public function findModelQuery($modelId = null): Builder
    {
        $query = $this->newQueryWithoutScopes();
        $ids   = $modelId ?? $this->modelKeyValue();

        if (is_array($ids) || $ids instanceof Arrayable) {
            return $query->whereIn(static::modelKeyName(), $ids);
        }

        return $query->where(static::modelKeyName(), $ids);
    }

    /**
     * @param mixed|null $modelId
     *
     * @return Model
     */
    public function findModelOrFail($modelId = null): Model
    {
        if ($modelId) {
            return $this->findModelQuery($modelId)->firstOrFail();
        }

        return $this->findModelQuery()->firstOrFail();
    }
}
