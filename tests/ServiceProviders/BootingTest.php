<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\ServiceProviders;

use Php\Support\Laravel\ServiceProviders\AbstractServiceProvider;
use Php\Support\Laravel\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class BootingTest extends AbstractTestCase
{
    #[Test]
    public function the_boot_method_is_resolved_per_provider_not_shared_between_subclasses(): void
    {
        // `static $method` inside an inherited method is shared with every subclass since
        // PHP 8.1, so the first provider to resolve used to decide for all the others.
        $inTesting = new FirstProvider($this->app);
        self::assertSame('bootPackageForTesting', $inTesting->exposedBootMethod());

        $app = $this->app;
        self::assertNotNull($app);
        $app['env'] = 'production';

        $inProduction = new SecondProvider($app);
        self::assertSame(
            'bootPackageForConsole',
            $inProduction->exposedBootMethod(),
            'The memoized boot method leaked from one provider class to another.'
        );
    }

    #[Test]
    public function two_instances_of_the_same_provider_do_not_share_the_cache(): void
    {
        $first = new FirstProvider($this->app);
        self::assertSame('bootPackageForTesting', $first->exposedBootMethod());

        $app = $this->app;
        self::assertNotNull($app);
        $app['env'] = 'production';

        $second = new FirstProvider($app);
        self::assertSame(
            'bootPackageForConsole',
            $second->exposedBootMethod(),
            'The memoized boot method leaked from one provider instance to another.'
        );

        self::assertSame(
            'bootPackageForTesting',
            $first->exposedBootMethod(),
            'An instance that already resolved must keep its own answer.'
        );
    }

    #[Test]
    public function a_provider_that_is_not_available_skips_the_whole_boot_chain(): void
    {
        UnavailableProvider::$trace = [];

        $provider = new UnavailableProvider($this->app);
        $provider->boot();
        $provider->callBootingCallbacks();
        $provider->callBootedCallbacks();

        self::assertSame([], UnavailableProvider::$trace);
    }

    #[Test]
    public function boot_runs_before_boot_then_the_runtime_hook_then_after_boot(): void
    {
        FirstProvider::$trace = [];

        (new FirstProvider($this->app))->boot();

        self::assertSame(
            [
                'before',
                'testing-server',
                'testing',
                'after',
            ],
            FirstProvider::$trace
        );
    }
}

abstract class TracingProvider extends AbstractServiceProvider
{
    /** @var string[] */
    public static array $trace = [];

    public function exposedBootMethod(): string
    {
        return $this->bootMethod();
    }

    protected static function packageSourcePath(): string
    {
        return __DIR__;
    }
}

class FirstProvider extends TracingProvider
{
    /** @var string[] */
    public static array $trace = [];

    protected function beforeBoot(): void
    {
        static::$trace[] = 'before';
    }

    protected function afterBoot(): void
    {
        static::$trace[] = 'after';
    }

    protected function bootForServer(): void
    {
        static::$trace[] = 'testing-server';
    }

    protected function bootForTesting(): void
    {
        static::$trace[] = 'testing';
    }
}

class SecondProvider extends TracingProvider
{
}

class UnavailableProvider extends TracingProvider
{
    /** @var string[] */
    public static array $trace = [];

    protected function availableToBoot(): bool
    {
        return false;
    }

    protected function beforeBoot(): void
    {
        static::$trace[] = 'before';
    }
}
