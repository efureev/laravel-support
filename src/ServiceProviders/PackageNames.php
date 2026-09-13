<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

use function array_slice;
use function explode;
use function implode;

/**
 * Naming helpers for a package service provider.
 *
 * @see \Php\Support\Laravel\ServiceProviders\AbstractServiceProvider
 */
trait PackageNames
{
    /**
     * The namespace the package lives in.
     *
     * Defaults to the namespace of the service provider itself. Declare a
     * `packageNamespace(): string` method on the provider to override it.
     *
     * ```php
     * // Acme\Billing\BillingServiceProvider  ->  'Acme\Billing'
     * BillingServiceProvider::getPackageNamespace();
     * ```
     */
    public static function getPackageNamespace(): string
    {
        if (method_exists(static::class, 'packageNamespace')) {
            return static::packageNamespace();
        }

        return implode('\\', array_slice(explode('\\', static::class), 0, -1));
    }

    /**
     * The package's short name, used as the prefix for configs, routes and translations.
     *
     * Defaults to the provider's FQCN. Declare a `PACKAGE_NS` constant to override it.
     *
     * ```php
     * class BillingServiceProvider extends AbstractServiceProvider
     * {
     *     public const PACKAGE_NS = 'billing';
     * }
     *
     * BillingServiceProvider::getPackageName(); // 'billing'
     * ```
     */
    public static function getPackageName(): string
    {
        if (defined($c = static::class . '::PACKAGE_NS')) {
            return (string)constant($c);
        }

        return static::class;
    }
}
