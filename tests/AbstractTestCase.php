<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Orchestra\Testbench\TestCase;
use Php\Support\Laravel\ServiceProvider;

/**
 * Class AbstractTestCase
 */
abstract class AbstractTestCase extends TestCase
{
    use InteractsWithDatabase;

    protected $migrations = [];

    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set(
            'database.connections.testing',
            [
                'driver'         => 'pgsql',
                'url'            => env('DATABASE_URL'),
                'host'           => env('DB_HOST', 'localhost'),
                'port'           => env('DB_PORT', '5432'),
                'database'       => env('POSTGRES_DB', 'postgres'),
                'username'       => env('DB_USERNAME', 'postgres'),
                'password'       => env('DB_PASSWORD', 'postgres'),
                'charset'        => 'utf8',
                'prefix'         => '',
                'prefix_indexes' => true,
                'schema'         => 'public',
                'sslmode'        => 'prefer',
            ]
        );

        $app['config']->set(
            'database.connections.sqlite',
            [
                'driver'   => 'sqlite',
                'host'     => '127.0.0.1',
                'port'     => '3306',
                'database' => __DIR__ . '/_data/database.sqlite',
            ]
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected static function databasePath(string $path = null): string
    {
        return __DIR__ . '/database' . ($path ? "/$path" : '');
    }

    protected static function migrationsPath(string $path = null): string
    {
        return self::databasePath('migrations' . ($path ? "/$path" : ''));
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:wipe');

        $this->installMigrations();
    }

    protected function installMigrations(): void
    {
        foreach ($this->migrations as $migration) {
            $this->loadMigrationsFrom(self::migrationsPath($migration));
        }
    }

    protected static function getProtectedMethod(string $class, string $name): \ReflectionMethod
    {
        $class  = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
