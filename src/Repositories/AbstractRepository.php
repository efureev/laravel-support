<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Php\Support\Laravel\Exceptions\InvalidParamException;

/**
 * Base Eloquent repository.
 *
 * Subclasses declare the model they wrap:
 *
 * ```php
 * final class PostRepository extends AbstractRepository
 * {
 *     protected string $modelClass = Post::class;
 * }
 * ```
 *
 * @see https://laravel.com/docs/13.x/eloquent
 */
abstract class AbstractRepository
{
    /** @var class-string<Model> */
    protected string $modelClass;

    protected Model $model;

    public function __construct()
    {
        $this->setModel($this->modelClass);
    }

    /**
     * @param class-string<Model> $modelName
     */
    protected function setModel(string $modelName): static
    {
        $this->model = new $modelName();

        return $this;
    }

    /**
     * @return Builder<Model>
     */
    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @return Collection<int, Model>
     */
    public function all(): Collection
    {
        return $this->query()->get();
    }

    /**
     * @param array<array-key, mixed>|string $relations
     *
     * @return Builder<Model>
     */
    public function with(array|string $relations): Builder
    {
        return $this->query()->with($relations);
    }

    /**
     * Resolve a model from its key, or pass an already-loaded model straight through.
     *
     * Accepts an `int` or `string` primary key, or a Model instance. Anything else is a
     * programming error and raises {@see InvalidParamException} — the check stays at runtime so
     * a loosely-typed caller gets a clear message instead of a TypeError.
     *
     * @param bool $throw throw {@see ModelNotFoundException} when nothing matches
     *
     * @throws InvalidParamException when `$id` is neither a scalar key nor a Model
     * @throws ModelNotFoundException when `$throw` is true and no row matches
     */
    public function findModel(mixed $id, bool $throw = true): ?Model
    {
        if ($id instanceof Model) {
            return $id;
        }

        if (is_int($id) || is_string($id)) {
            $key   = $id;
            $model = $this->query()->find($key);
        } else {
            throw new InvalidParamException(
                sprintf('$id must be int, string or %s, got %s', Model::class, get_debug_type($id)),
                'id'
            );
        }

        if ($throw && !$model) {
            throw (new ModelNotFoundException())->setModel($this->model::class, $key);
        }

        return $model;
    }

    /**
     * Delete the row identified by `$id`.
     *
     * @param int|string|Model $id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException when nothing matches
     */
    protected function deleteModel(mixed $id): void
    {
        $this->findModel($id)?->delete();
    }

    /**
     * Delete the row identified by `$id`.
     *
     * @param int|string|Model $id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException when nothing matches
     */
    public function delete(mixed $id): void
    {
        $this->deleteModel($id);
    }

    /**
     * Update `$model` with `$attributes`, or create a new row when `$model` is null.
     *
     * @param array<string, mixed> $attributes
     * @param int|string|Model|null $model
     */
    protected function storeModel(array $attributes, mixed $model = null): Model
    {
        if (!$model) {
            return $this->createModel($attributes);
        }

        $model = $this->findModel($model);
        // findModel() only returns null when $throw is false, which it is not here.
        $model->update($attributes);

        return $model;
    }

    /**
     * Update `$model` with `$attributes`, or create a new row when `$model` is null.
     *
     * @param array<string, mixed> $attributes
     * @param int|string|Model|null $model
     */
    public function store(array $attributes, mixed $model = null): Model
    {
        return $this->storeModel($attributes, $model);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function createModel(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }
}
