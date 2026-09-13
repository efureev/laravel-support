<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\ServiceProviders;

use Illuminate\Support\Facades\Config;
use Php\Support\Laravel\ServiceProviders\AbstractServiceProvider;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\TestClasses\ServiceProviders\ExampleManager;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class HasRegistersTest extends AbstractTestCase
{
    #[Test]
    public function it_merges_a_package_config_under_the_requested_key(): void
    {
        $provider = new RegisteringProvider($this->app);
        $provider->register();

        self::assertSame('example', Config::get('example.name'));
    }

    #[Test]
    public function it_can_replace_a_config_instead_of_merging(): void
    {
        Config::set('example', ['name' => 'stale', 'extra' => 'kept-on-merge']);

        $provider = new RegisteringProvider($this->app);
        $provider->replaceConfig();

        self::assertSame('example', Config::get('example.name'));
        self::assertNull(Config::get('example.extra'), 'Replacing must not keep the old keys.');
    }

    #[Test]
    public function a_missing_config_file_is_reported_instead_of_requiring_null(): void
    {
        // `require null` / `mergeConfigFrom(null, ...)` used to surface as an opaque TypeError
        // deep inside the framework.
        $provider = new RegisteringProvider($this->app);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config file "nope.php" not found');

        $provider->registerMissingConfig();
    }

    #[Test]
    public function a_missing_route_file_is_reported_instead_of_loading_null(): void
    {
        $provider = new RegisteringProvider($this->app);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route file "nope.php" not found');

        $provider->registerMissingRoutes();
    }

    #[Test]
    public function it_loads_the_packages_route_files(): void
    {
        $provider = new RegisteringProvider($this->app);
        $provider->registerExistingRoutes();

        /** @var \Illuminate\Routing\Route[] $routes */
        $routes = $this->app['router']->getRoutes()->getRoutes();

        $uris = array_map(static fn(\Illuminate\Routing\Route $route): string => $route->uri(), $routes);

        self::assertContains('front-api/test', $uris);
        self::assertContains('back-api/test', $uris);
    }

    #[Test]
    public function it_binds_and_aliases_a_service(): void
    {
        $provider = new RegisteringProvider($this->app);
        $provider->registerServices();

        self::assertInstanceOf(ExampleManager::class, $this->app->make('example.manager'));
    }

    #[Test]
    public function migrations_can_be_switched_off(): void
    {
        $provider = new RegisteringProvider($this->app);

        $provider->ignoreMigrations();
        self::assertFalse($provider->migrationsEnabled());

        $provider->runMigrations();
        self::assertTrue($provider->migrationsEnabled());
    }
}

class RegisteringProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->registerConfig('example');
    }

    public function replaceConfig(): void
    {
        $this->registerConfig(['example' => 'example'], true);
    }

    public function registerMissingConfig(): void
    {
        $this->registerConfig('nope');
    }

    public function registerMissingRoutes(): void
    {
        $this->registerRoutes('nope');
    }

    public function registerExistingRoutes(): void
    {
        $this->registerRoutes(['front-api', 'back-api']);
    }

    public function registerServices(): void
    {
        $this->registerService(ExampleManager::class, 'example.manager');
    }

    public function migrationsEnabled(): bool
    {
        return $this->runMigration;
    }

    protected static function packageSourcePath(): string
    {
        return dirname(__DIR__) . '/TestClasses/ServiceProviders/src';
    }
}
