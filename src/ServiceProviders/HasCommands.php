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
    /** @var class-string[] */
    protected static array $commands = [];

    protected function registerCommands(): static
    {
        return $this->registerCommandsForce(static::$commands);
    }

    /**
     * @param class-string[] $commands
     */
    protected function registerCommandsForce(array $commands): static
    {
        $this->commands($commands);

        return $this;
    }
}
