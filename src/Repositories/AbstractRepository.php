<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Php\Support\Exceptions\InvalidParamException;

abstract class AbstractRepository
{
    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var Model
     */
    protected $model;

    public function __construct()
    {
        $this->setModel($this->modelClass);
    }

    protected function setModel(string $modelName): self
    {
        $this->model = new $modelName();

        return $this;
    }

    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function all(): Collection
    {
        return $this->query()->get();
    }

    public function with($relations): Builder
    {
        return $this->query()->with($relations);
    }

    /**
     * @param string|Model $id
     * @param bool $throw
     *
     * @return Model|null
     */
    public function findModel($id, bool $throw = true): ?Model
    {
        if (is_string($id)) {
            $model = $this->query()->find($id);
        } elseif ($id instanceof Model) {
            $model = $id;
        } else {
            throw new InvalidParamException('{$id} must be string or Model instance');
        }

        if ($throw && !$model) {
            throw (new ModelNotFoundException())->setModel(get_class($this->model), $model);
        }

        return $model;
    }

    protected function deleteModel($id): void
    {
        $this->findModel($id)->delete();
    }

    /**
     * @param $id
     *
     * return mixed
     */
    public function delete($id)
    {
        $this->deleteModel($id);
    }

    /**
     * @param array $attribute
     * @param string|Model|null $model
     *
     * @return Model
     */
    protected function storeModel(array $attribute, $model = null): Model
    {
        if (!$model) {
            return $this->createModel($attribute);
        }

        $model = $this->findModel($model);
        $model->update($attribute);

        return $model;
    }

    public function store(array $attribute, $model = null): Model
    {
        return $this->storeModel($attribute, $model);
    }

    protected function createModel(array $attribute): Model
    {
        return $this->query()->create($attribute);
    }
}
