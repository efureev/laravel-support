<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait Modelable
{
    /**
     * @return string
     */
    abstract protected static function modelClass(): string;

    protected static function modelKeyName(): string
    {
        return static::getModelInstance()->getQualifiedKeyName();
    }

    /**
     * @param array $attributes
     *
     * @return Model|Builder
     */
    public static function getModelInstance(array $attributes = []): Model
    {
        $class = static::modelClass();
        return new $class($attributes);
    }

    protected $modelInstance;

    public function model($new = false): Model
    {
        if (!$this->modelInstance || $new) {
            $this->modelInstance = static::getModelInstance();
        }

        return $this->modelInstance;
    }
}
