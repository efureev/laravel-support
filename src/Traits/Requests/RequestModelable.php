<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Requests;

use Illuminate\Http\Request;
use Php\Support\Laravel\Traits\ModelQueryable;

/**
 * @mixin Request
 */
trait RequestModelable
{
    use ModelQueryable;

    protected function modelKeyValueGainer(): callable
    {
        return function ($key) {
            return $this->input($key);
        };
    }
}
