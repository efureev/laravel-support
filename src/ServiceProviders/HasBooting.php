<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

use Illuminate\Support\ServiceProvider;

/**
 * Trait HasBooting
 * @package Php\Support\Laravel
 *
 * @mixin ServiceProvider
 */
trait HasBooting
{
    private function bootMethod(): string
    {
        static $method;
        if (!$method) {
            $result = match (true) {
                $this->app->environment('testing') => 'Testing',
                $this->app->runningInConsole() => 'Console',
                default => 'Server',
            };

            $method = "bootPackageFor$result";
        }

        return $method;
    }

    private ?bool $availableToBootCache = null;

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
