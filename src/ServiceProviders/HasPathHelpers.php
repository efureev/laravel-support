<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

use Illuminate\Support\Arr;

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
     * @param string|null $path
     *
     * @return string|null
     */
    public static function getDatabaseSeedersPath(?string $path = null): ?string
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
    public static function getConfigPath(?string $path = null): ?string
    {
        if (!$cPath = static::packagePath('config')) {
            return null;
        }

        return $cPath . ($path ? '/' . trim($path, '/') : '');
    }

    public static function getRoutesPath(?string $path = null): ?string
    {
        if (!$cPath = static::packagePath('routes')) {
            return null;
        }

        return $cPath . ($path ? '/' . trim($path, '/') : '');
    }

    public static function getResourcesPath(?string $path = null): ?string
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
        return (string)(
            static::getVersionFromFile(static::packagePath('version.json')) ??
            static::getVersionFromFile(static::packagePath('composer.json'))
        );
    }

    /**
     * Read a dot-notation `$key` out of a JSON file.
     *
     * Returns `null` for a missing, unreadable or malformed file rather than throwing —
     * a package without a `version.json` is normal, not an error.
     */
    protected static function getVersionFromFile(?string $filePath, string $key = 'version'): ?string
    {
        if ($filePath === null || !is_file($filePath)) {
            return null;
        }

        $contents = @file_get_contents($filePath);

        if ($contents === false) {
            return null;
        }

        $data = json_decode($contents, true);

        if (!is_array($data)) {
            return null;
        }

        $value = Arr::get($data, $key);

        return is_scalar($value) ? (string)$value : null;
    }
}
