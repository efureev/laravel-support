<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

trait PackageNames
{
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
     * Package's name
     *
     * @return string
     */
    public static function getPackageName(): string
    {
        if (defined($c = static::class . '::PACKAGE_NS')) {
            return constant($c);
        }

        return static::class;
    }
}
