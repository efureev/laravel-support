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
                'database'       => 'testing',
                'username'       => env('DB_USERNAME', 'postgres'),
                'password'       => env('DB_PASSWORD', ''),
                'charset'        => 'utf8',
                'prefix'         => '',
                'prefix_indexes' => true,
                'schema'         => 'public',
                'sslmode'        => 'prefer',
            ]
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:wipe');
    }
}
