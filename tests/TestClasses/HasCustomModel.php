<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses;

class HasCustomModel extends HasModel
{
    protected function modelKeyValueGainer(): callable
    {
        return function ($key) {
            return $this->input($key);
        };
    }

    private function input($key)
    {
        return 2;
    }
}
