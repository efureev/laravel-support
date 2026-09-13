<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Binds an Eloquent model into a plain class.
 *
 * The using class names the model:
 *
 * ```php
 * class TagRequest extends FormRequest
 * {
 *     use Modelable;
 *
 *     protected static function modelClass(): string
 *     {
 *         return Tag::class;
 *     }
 * }
 * ```
 */
trait Modelable
{
    /**
     * @return class-string<Model>
     */
    abstract protected static function modelClass(): string;

    /**
     * The model's key **qualified with its table** (`tags.id`) — safe to use in a WHERE clause
     * across joins.
     *
     * @see https://laravel.com/docs/13.x/eloquent#primary-keys
     */
    protected static function modelKeyName(): string
    {
        return static::getModelInstance()->getQualifiedKeyName();
    }

    /**
     * The model's key **unqualified** (`id`) — the name an outside source (request input, route
     * parameter, array key) would use. Override it when the outside name differs from the
     * column name.
     */
    protected static function modelInputKeyName(): string
    {
        return static::getModelInstance()->getKeyName();
    }

    /**
     * A fresh, unsaved instance of the bound model.
     *
     * @param array<string, mixed> $attributes
     */
    public static function getModelInstance(array $attributes = []): Model
    {
        $class = static::modelClass();

        return new $class($attributes);
    }

    protected ?Model $modelInstance = null;

    /**
     * The memoized model instance; pass `true` to build a fresh one.
     */
    public function model(bool $new = false): Model
    {
        if (!$this->modelInstance || $new) {
            $this->modelInstance = static::getModelInstance();
        }

        return $this->modelInstance;
    }
}
