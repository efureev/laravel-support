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

    /** @var string[] */
    protected array $migrations = [];

    /**
     * Read a connection setting from the real environment.
     *
     * `env()` is the wrong tool outside `config/`: it returns null once the config is cached.
     * Here the values come straight from docker-compose / the CI job env.
     */
    protected static function envValue(string $name, string $default): string
    {
        $value = getenv($name);

        return $value === false || $value === '' ? $default : $value;
    }

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
                'url'            => getenv('DATABASE_URL') ?: null,
                'host'           => self::envValue('DB_HOST', 'localhost'),
                'port'           => self::envValue('DB_PORT', '5432'),
                'database'       => self::envValue('DB_DATABASE', 'forge'),
                'username'       => self::envValue('DB_USERNAME', 'forge'),
                'password'       => self::envValue('DB_PASSWORD', 'forge'),
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

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected static function databasePath(?string $path = null): string
    {
        return __DIR__ . '/database' . ($path ? "/$path" : '');
    }

    protected static function migrationsPath(?string $path = null): string
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

    /**
     * @param class-string $class
     */
    protected static function getProtectedMethod(string $class, string $name): \ReflectionMethod
    {
        return (new \ReflectionClass($class))->getMethod($name);
    }
}
