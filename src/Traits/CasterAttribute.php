<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits;

/**
 * Trait CasterAttribute
 * @package Php\Support\Traits\Laravel
 */
trait CasterAttribute
{
    protected function castAttribute($key, $value)
    {
        $res = parent::castAttribute($key, $value);

        $type = $this->getCasts()[$key];

        if (class_exists($type) && method_exists($type, 'cast')) {
            return $type::cast($value);
        }

        return $res;
    }
}
