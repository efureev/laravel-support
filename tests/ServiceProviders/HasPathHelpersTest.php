<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\ServiceProviders;

use Php\Support\Laravel\ServiceProviders\AbstractServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HasPathHelpersTest extends TestCase
{
    private static function fixtureRoot(): string
    {
        return dirname(__DIR__) . '/TestClasses/ServiceProviders';
    }

    #[Test]
    public function the_root_is_the_parent_of_the_source_path(): void
    {
        self::assertSame(self::fixtureRoot(), FixtureProvider::packageRootPath());
    }

    #[Test]
    public function package_path_returns_null_for_something_unreadable(): void
    {
        self::assertNull(FixtureProvider::packagePath('nope'));
        self::assertSame(self::fixtureRoot() . '/config', FixtureProvider::packagePath('config'));
    }

    #[Test]
    public function directory_helpers_return_null_when_the_directory_is_absent(): void
    {
        self::assertNull(FixtureProvider::getMigrationsPath());
        self::assertNull(FixtureProvider::getViewsPath());
        self::assertNull(FixtureProvider::getTranslationsPath());
        self::assertNull(FixtureProvider::getDatabaseSeedersPath());
    }

    #[Test]
    public function the_seeders_path_takes_an_optional_argument_like_its_siblings(): void
    {
        // It used to be the only helper of the family with a required parameter.
        self::assertNull(FixtureProvider::getDatabaseSeedersPath());
        self::assertNull(FixtureProvider::getDatabaseSeedersPath('Some.php'));
    }

    #[Test]
    public function directory_helpers_append_the_given_sub_path(): void
    {
        $root = self::fixtureRoot();

        self::assertSame("$root/config", FixtureProvider::getConfigPath());
        self::assertSame("$root/config/example.php", FixtureProvider::getConfigPath('example.php'));
        self::assertSame("$root/routes", FixtureProvider::getRoutesPath());
        self::assertSame("$root/routes/front-api.php", FixtureProvider::getRoutesPath('front-api.php'));
    }

    #[Test]
    public function the_version_is_read_from_a_json_file(): void
    {
        self::assertSame('', FixtureProvider::version(), 'No version.json and no composer.json here.');
        self::assertSame('9.9.9', VersionedProvider::version());
    }

    #[Test]
    public function a_malformed_or_missing_json_file_yields_null_rather_than_throwing(): void
    {
        self::assertNull(VersionedProvider::readVersionFrom(null));
        self::assertNull(VersionedProvider::readVersionFrom('/no/such/file.json'));
        self::assertNull(VersionedProvider::readVersionFrom(__FILE__), 'This file is PHP, not JSON.');
    }

    #[Test]
    public function a_missing_key_yields_null(): void
    {
        self::assertNull(
            VersionedProvider::readVersionFrom(VersionedProvider::packagePath('version.json'), 'nope')
        );
    }
}

class FixtureProvider extends AbstractServiceProvider
{
    protected static function packageSourcePath(): string
    {
        return dirname(__DIR__) . '/TestClasses/ServiceProviders/src';
    }
}

class VersionedProvider extends AbstractServiceProvider
{
    public static function readVersionFrom(?string $path, string $key = 'version'): ?string
    {
        return static::getVersionFromFile($path, $key);
    }

    protected static function packageSourcePath(): string
    {
        return dirname(__DIR__) . '/TestClasses/Versioned/src';
    }
}
