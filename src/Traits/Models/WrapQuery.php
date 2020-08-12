<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Models;

/**
 * Trait WrapQuery
 * @package Php\Support\Laravel\Traits\Models
 *
 * @method static wrapQuery(?callable $callback = null)
 */
trait WrapQuery
{
    /**
     * @param $query
     * @param callable|null $callback
     *
     * @example
     * $cb = fn(Builder $query) => $query->where('enabled', true);
     * Model::wrapQuery($cb)->wrapQuery()->wrapQuery(...)->get();
     */
    public function scopeWrapQuery($query, ?callable $callback = null)
    {
        if ($callback) {
            $callback($query);
        }
    }
}
