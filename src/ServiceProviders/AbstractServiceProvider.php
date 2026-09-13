<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

use Illuminate\Support\ServiceProvider;

/**
 * Base service provider for a package: aggregates the naming, policy, command, booting and
 * registration helpers, and dispatches `boot()` to the runtime-specific hook.
 *
 * Boot order is `beforeBoot()` -> `bootPackageForServer|Console|Testing()` -> `afterBoot()`,
 * and the whole chain is skipped when {@see HasBooting::availableToBoot()} returns false.
 *
 * @see https://laravel.com/docs/13.x/packages
 * @see \Php\Support\Laravel\ServiceProviders\HasBooting
 */
abstract class AbstractServiceProvider extends ServiceProvider
{
    use PackageNames;
    use HasPolicies;
    use HasCommands;
    use HasBooting;
    use HasRegisters;

    public function boot(): void
    {
        if (!$this->resolveAvailableToBoot()) {
            return;
        }

        $this->beforeBoot();

        $this->{$this->bootMethod()}();

        $this->afterBoot();
    }

    public function callBootingCallbacks(): void
    {
        if (!$this->resolveAvailableToBoot()) {
            return;
        }

        parent::callBootingCallbacks();
    }

    public function callBootedCallbacks(): void
    {
        if (!$this->resolveAvailableToBoot()) {
            return;
        }

        parent::callBootedCallbacks();
    }
}
