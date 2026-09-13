<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Unit;

use Orchestra\Testbench\TestCase;

abstract class AbstractUnitTestCase extends TestCase
{
    /**
     * @param class-string $class
     */
    protected static function getProtectedMethod(string $class, string $name): \ReflectionMethod
    {
        return (new \ReflectionClass($class))->getMethod($name);
    }
}
