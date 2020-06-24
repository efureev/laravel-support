<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

if (!function_exists('user')) {
    /**
     * Returns the current user authenticated, or `null`
     *
     * @param string|null $guard
     *
     * @return null|\Illuminate\Contracts\Auth\Authenticatable
     */
    function user($guard = null)
    {
        return app('auth')->guard($guard)->user();
    }
}

if (!function_exists('toCollect')) {
    /**
     * @param mixed $model
     *
     * @return Collection
     */
    function toCollect($model)
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
