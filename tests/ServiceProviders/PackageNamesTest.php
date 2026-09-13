<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\ServiceProviders;

use Php\Support\Laravel\ServiceProviders\AbstractServiceProvider;
use Php\Support\Laravel\Tests\TestClasses\ServiceProviders\ServiceProvider as ExampleProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PackageNamesTest extends TestCase
{
    #[Test]
    public function the_namespace_defaults_to_the_providers_own_namespace(): void
    {
        self::assertSame(__NAMESPACE__, PlainProvider::getPackageNamespace());
    }

    #[Test]
    public function the_namespace_can_be_overridden_by_a_method(): void
    {
        self::assertSame('Acme\\Custom', CustomNamespaceProvider::getPackageNamespace());
    }

    #[Test]
    public function the_name_defaults_to_the_providers_fqcn(): void
    {
        self::assertSame(PlainProvider::class, PlainProvider::getPackageName());
    }

    #[Test]
    public function the_name_comes_from_the_package_ns_constant_when_declared(): void
    {
        self::assertSame('example', ExampleProvider::getPackageName());
    }
}

class PlainProvider extends AbstractServiceProvider
{
    protected static function packageSourcePath(): string
    {
        return __DIR__;
    }
}

class CustomNamespaceProvider extends AbstractServiceProvider
{
    public static function packageNamespace(): string
    {
        return 'Acme\\Custom';
    }

    protected static function packageSourcePath(): string
    {
        return __DIR__;
    }
}
