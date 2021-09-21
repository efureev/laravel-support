<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Unit;

use Orchestra\Testbench\TestCase;

abstract class AbstractUnitTestCase extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }


    protected static function getProtectedMethod(string $class, string $name): \ReflectionMethod
    {
        $class  = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
