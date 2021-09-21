<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

use Illuminate\Support\Arr;
use Php\Support\Helpers\Json;

trait HasPathHelpers
{
    /**
     * A package's source path
     *
     * @return string
     */
    abstract protected static function packageSourcePath(): string;

    /**
     * Return package root path
     *
     * @return string
     */
    public static function packageRootPath(): string
    {
        return dirname(static::packageSourcePath());
    }

    /**
     * @param string $path
     *
     * @return string|null
     */
    public static function packagePath(string $path): ?string
    {
        $path = static::packageRootPath() . ($path ? '/' . trim($path, '/') : '');

        return is_readable($path) ? $path : null;
    }

    /**
     * Package's migration path
     *
     * @return string|null
     */
    public static function getMigrationsPath(): ?string
    {
        return static::packagePath('database/migrations');
    }


    /**
     * Package's seeders path
     *
     * @param string $path
     *
     * @return string|null
     */
    public static function getDatabaseSeedersPath(string $path): ?string
    {
        if (!$cPath = static::packagePath('database/seeders')) {
            return null;
        }

        return $cPath . ($path ? '/' . trim($path, '/') : '');
    }

    /**
     * Package's config path
     *
     * @param string|null $path
     *
     * @return string|null
     */
    public static function getConfigPath(string $path = null): ?string
    {
        if (!$cPath = static::packagePath('config')) {
            return null;
        }

        return $cPath . ($path ? '/' . trim($path, '/') : '');
    }

    public static function getRoutesPath(string $path = null): ?string
    {
        if (!$cPath = static::packagePath('routes')) {
            return null;
        }

        return $cPath . ($path ? '/' . trim($path, '/') : '');
    }

    public static function getResourcesPath(string $path = null): ?string
    {
        if (!$cPath = static::packagePath('resources')) {
            return null;
        }

        return $cPath . ($path ? '/' . trim($path, '/') : '');
    }

    /**
     * Путь переводов
     *
     * @return string|null
     */
    public static function getTranslationsPath(): ?string
    {
        return static::packagePath('resources/lang');
    }

    /**
     * Views path
     *
     * @return string|null
     */
    public static function getViewsPath(): ?string
    {
        return static::packagePath('resources/views');
    }

    /**
     * Версия пакета
     *
     * @return string
     */
    public static function version(): string
    {
        $pathComposer = static::packagePath('composer.json');
        if (file_exists($pathComposer)) {
            $composerData = Json::decode(file_get_contents($pathComposer));
            if ($version = Arr::get($composerData, 'version', '')) {
                return $version;
            }
        }

        return '';
    }
}
