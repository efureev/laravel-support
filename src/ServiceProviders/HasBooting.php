<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

use Illuminate\Support\ServiceProvider;

/**
 * Runtime-aware boot hooks for a package service provider.
 *
 * @mixin ServiceProvider
 */
trait HasBooting
{
    private ?string $bootMethodCache = null;

    private ?bool $availableToBootCache = null;

    /**
     * Which `bootPackageFor*()` variant applies to the current runtime.
     *
     * Memoized per provider instance. A `static` local would be shared with every subclass that
     * inherits this method (PHP >= 8.1), which leaks the first provider's answer to the rest.
     *
     * @see https://www.php.net/manual/en/language.variables.scope.php#language.variables.scope.static
     */
    protected function bootMethod(): string
    {
        return $this->bootMethodCache ??= 'bootPackageFor' . match (true) {
            $this->app->environment('testing') => 'Testing',
            $this->app->runningInConsole() => 'Console',
            default => 'Server',
        };
    }

    protected function resolveAvailableToBoot(): bool
    {
        if ($this->availableToBootCache === null) {
            $this->availableToBootCache = $this->availableToBoot();
        }
        return $this->availableToBootCache;
    }

    protected function availableToBoot(): bool
    {
        return true;
    }

    protected function beforeBoot(): void
    {
    }

    protected function afterBoot(): void
    {
    }

    protected function bootPackageForServer(): void
    {
        $this->bootForServer();
    }


    protected function bootPackageForTesting(): void
    {
        $this->bootPackageForServer();

        if ($this->app->runningInConsole()) {
            $this->bootPackageForConsole();
        }

        $this->bootForTesting();
    }


    protected function bootPackageForConsole(): void
    {
        $this
            ->registerMigrations()
            ->registerCommands()
            ->bootForConsole();
    }

    protected function bootForServer(): void
    {
    }

    protected function bootForTesting(): void
    {
    }

    protected function bootForConsole(): void
    {
    }
}
