<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\ServiceProviders;

use Illuminate\Support\Facades\Artisan;
use Php\Support\Laravel\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Extends the plain testbench case on purpose: {@see \Php\Support\Laravel\Tests\AbstractTestCase}
 * runs `artisan db:wipe` in setUp, which builds the console application — and
 * `ServiceProvider::commands()` defers through `Artisan::starting()`, whose callbacks only
 * run while that application is still being built.
 */
class HasCommandsTest extends AbstractUnitTestCase
{
    #[Test]
    public function declared_commands_are_registered_with_artisan(): void
    {
        (new PolicyProvider($this->app))->registerDeclaredCommands();

        // ServiceProvider::commands() defers to Artisan::starting(), so the command only
        // materialises once the console application is built.
        self::assertArrayHasKey('example:noop', Artisan::all());
    }

    #[Test]
    public function commands_can_be_registered_ad_hoc(): void
    {
        (new PolicyProvider($this->app))->registerExtraCommands([SecondNoopCommand::class]);

        self::assertArrayHasKey('example:noop-2', Artisan::all());
    }

    #[Test]
    public function registering_commands_is_chainable(): void
    {
        $provider = new PolicyProvider($this->app);

        self::assertSame($provider, $provider->registerDeclaredCommands());
    }
}
