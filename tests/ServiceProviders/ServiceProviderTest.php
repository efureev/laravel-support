<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\ServiceProviders;

use Php\Support\Laravel\ServiceProviders\AbstractServiceProvider;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\TestClasses\ServiceProviders\ServiceProvider;

class ServiceProviderTest extends AbstractTestCase
{
    public function testInit(): void
    {
        $sp = new ServiceProvider($this->app);

        static::assertInstanceOf(AbstractServiceProvider::class, $sp);
    }

    public function testPaths(): void
    {
        $sp = new ServiceProvider($this->app);

        $dir = dirname(__DIR__) . '/TestClasses/ServiceProviders';

        static::assertEquals("$dir/config", $sp::getConfigPath());
        static::assertEquals("$dir/config/example.php", $sp::getConfigPath('example.php'));
        static::assertEquals("$dir/resources", $sp::getResourcesPath());

        static::assertNull($sp::getMigrationsPath());
        static::assertNull($sp::getViewsPath());
    }

    public function testRegisters(): void
    {
        $this->app->register(ServiceProvider::class);
        $sp = $this->app->getProvider(ServiceProvider::class);

        static::assertInstanceOf(AbstractServiceProvider::class, $sp);
    }
}
