<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

use Illuminate\Support\ServiceProvider;

/**
 * Class AbstractAliceServiceProvider
 * @package Sitesoft\Alice\Essentials\Supplies
 *
 * @method bootPackageForServer()
 * @method bootPackageForConsole()
 * @method bootPackageForTesting()
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

    public function callBootingCallbacks()
    {
        if (!$this->resolveAvailableToBoot()) {
            return;
        }

        parent::callBootingCallbacks();
    }

    public function callBootedCallbacks()
    {
        if (!$this->resolveAvailableToBoot()) {
            return;
        }

        parent::callBootedCallbacks();
    }
}
