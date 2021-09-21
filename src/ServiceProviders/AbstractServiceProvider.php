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

    /**
     * Defines NS for the package
     *
     * @return string
     */
    public static function getPackageNamespace(): string
    {
        if (method_exists(static::class, 'packageNamespace')) {
            return static::packageNamespace();
        }

        return classNamespace(static::class);
    }

    /**
     * Package name
     *
     * @return string
     */
    public function getPackageName(): string
    {
        if (defined($this::class . '::PACKAGE_NS')) {
            return static::PACKAGE_NS;
        }

        return $this::class;
    }
}
