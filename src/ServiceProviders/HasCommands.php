<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

use Illuminate\Support\ServiceProvider;

/**
 * Trait HasCommands
 * @package Php\Support\Laravel\ServiceProviders
 *
 * @mixin ServiceProvider
 */
trait HasCommands
{
    /** @var array <Command::class> $commands */
    protected static array $commands = [];

    protected function registerCommands(): static
    {
        return $this->registerCommandsForce(static::$commands);
    }

    /**
     * @param array <Command::class>  $policies
     *
     * @return $this
     */
    protected function registerCommandsForce(array $commands): static
    {
        $this->commands($commands);

        return $this;
    }
}
