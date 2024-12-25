<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Trait HasRegisters
 * @mixin ServiceProvider
 */
trait HasRegisters
{
    use HasPathHelpers;

    protected function onEvent(string|array $event, Closure|string $callback): static
    {
        Event::listen($event, $callback);

        return $this;
    }


    /**
     * Load configs
     *
     * @param array|string $configs
     * @param bool $needReplace
     *
     * @return $this
     *
     * @example 1
     *  It'll load config file from `config/language.php` into app config key `language`.
     *      $this->registerConfig('language')
     *
     * @example 2
     *  It'll load two config files from `config/config1.php` && `config/sites.php`
     *  into app config key `redis.custom` and `sites`.
     *      $this->registerConfig(['config1'=>'redis.custom','sites'=>'sites'])
     *
     * @example 1
     *  It'll load config file from `config/language.php` into app config key `language` and replace it if it exists
     *      $this->registerConfig('language')
     */
    protected function registerConfig(array|string $configs, bool $needReplace = false): static
    {
        foreach ((array)$configs as $configFile => $configKey) {
            if (is_int($configFile)) {
                $configFile = $configKey;
            }

            $configPath = static::getConfigPath("$configFile.php");

            $needReplace ? Config::set($configKey, require $configPath) : $this->mergeConfigFrom(
                $configPath,
                $configKey
            );
        }

        return $this;
    }

    /**
     * Register package's routes
     *
     * @param array|string $routes
     *
     * @return $this
     *
     * @example
     *  $this->registerRoutes('api-front')
     *  $this->registerRoutes(['api-front','api-back'])
     */
    protected function registerRoutes(array|string $routes): static
    {
        foreach ((array)$routes as $route) {
            $this->loadRoutesFrom(static::getRoutesPath("$route.php"));
        }

        return $this;
    }

    /**
     * Register package's translations into $namespace key
     *
     * @param string $namespace
     *
     * @return $this
     */
    protected function registerTranslations(string $namespace): static
    {
        if ($path = static::getTranslationsPath()) {
            $this->loadTranslationsFrom($path, $namespace);
        }

        return $this;
    }

    protected function registerViews(string $namespace): static
    {
        if ($path = static::getViewsPath()) {
            $this->loadViewsFrom($path, $namespace);
        }

        return $this;
    }

    /**
     * Register any service in package
     *
     * @param string $serviceClass
     * @param Closure|string|null $serviceName
     * @param bool $singleton
     * @param string|null $alias
     *
     * @return $this
     *
     * @example 1
     *  It creates instance of the `Languages::class` and binding: `app(Languages::class)`
     *  $this->registerService(Languages::class)
     *
     * @example 2
     * It creates instance of the `Languages::class` and bindings: `app(Languages::class), app('languages')`
     *  $this->registerService(Languages::class, 'languages')
     *
     * @example 3
     * It creates singleton instance of the `Languages::class` and bindings: `app(Languages::class), app('languages')`
     *  $this->registerService(Languages::class, 'languages', true)
     *
     * @example 4
     * It creates instance of the `Languages::class` with custom init,
     * and bindings: `app(Languages::class), app('languages')`
     *  $this->registerService(
     *      Languages::class,
     *      fn($app) => new Languages(config('languages.default', 'ru')),
     *      false,
     *     'languages')
     *
     * @example 5
     * It creates instance of the `T2` and store it by a path `T1`. It helps you to overwrite some abstracts.
     *  $this->registerService(T1::class, T2:class)
     */
    protected function registerService(
        string $serviceClass,
        Closure|string|null $serviceName = null,
        bool $singleton = false,
        ?string $alias = null
    ): static {
        if (is_string($serviceName)) {
            if (class_exists($serviceName)) {
                $concrete = $serviceName;
            } else {
                $concrete = $serviceClass;

                if (!$alias && $serviceClass !== $serviceName) {
                    $alias = $serviceName;
                }
            }
        } else {
            $concrete = $serviceName;
        }

        $this->app->bind($serviceClass, $concrete, $singleton);

        if ($alias) {
            $this->app->alias($serviceClass, $alias);
        }

        return $this;
    }


    protected function registerInstance(string $abstract, mixed $instance, ?string $alias = null): static
    {
        $this->app->instance($abstract, $instance);

        if ($alias) {
            $this->app->alias($abstract, $alias);
        }

        return $this;
    }

    protected bool $runMigration = true;

    public function ignoreMigrations(): void
    {
        $this->runMigration = false;
    }

    public function runMigrations(bool $enable = true): void
    {
        $this->runMigration = $enable;
    }

    protected function registerMigrations(): static
    {
        if ($this->runMigration && ($path = self::getMigrationsPath())) {
            $this->loadMigrationsFrom($path);
        }

        return $this;
    }
}
