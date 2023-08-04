# Service Providers

List of traits

* `HasCommands` - contains methods for easy-register console commands
* `HasPolicies` - contains methods for easy-register policies
* `HasPathHelpers` - contains helper-methods for work with package's paths
* `HasRegisters` - contains helper-methods for flexibly registration any elements inside SP
* `HasBooting` - contains helper-methods for flexibly loading sides: console, server, testing

Class `AbstractServiceProvider` aggregates all these traits

## Example

```php
<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\ServiceProviders;

use Php\Support\Laravel\ServiceProviders\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider
{
    public const PACKAGE_NS = 'example';

    protected static array $policies = [
         Model::class => ModelPolicy::class,
    ];

    protected static array $commands = [
         Command::class,
         Command2::class,
    ];

    public function register(): void
    {
         $this
           ->registerService(Service::class, self::PACKAGE_NS)
           ->registerService(Service2::class, self::PACKAGE_NS."2", true);

    }

    protected function beforeBoot(): void
    {
        $this
            ->registerConfig(self::PACKAGE_NS)
            ->registerTranslations(self::PACKAGE_NS);
    }

    protected function bootForServer(): void
    {
        $this
            ->registerRoutes(['front-api', 'back-api'])
            ->registerPolicies();
    }

    protected function bootForConsole(): void
    {
        $this
            ->registerService(
                ExampleManager::class,
                self::PACKAGE_NS . '.manager'
            )
            ->onEvent(
                Event::class,
                fn(Event $event) => $event
            )
            ->publishes(
                [
                    // self::getTranslationsPath() => resource_path('lang/vendor/' . self::PACKAGE_NS),
                ],
                [self::PACKAGE_NS]
            );
    }

    protected static function packageSourcePath(): string
    {
        return __DIR__;
    }
}
```
