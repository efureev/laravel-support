<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\TestClasses\ServiceProviders;

use Php\Support\Laravel\ServiceProviders\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider
{
    public const PACKAGE_NS = 'example';

    /** @var array<class-string, class-string> */
    protected static array $policies = [];

    /** @var class-string[] */
    protected static array $commands = [];

    public function register(): void
    {
        // $this
        //   ->registerService(Service::class, self::PACKAGE_NS)
        //   ->registerService(Service2::class, self::PACKAGE_NS."2", true);
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
            ->publishes([], [self::PACKAGE_NS]);
    }

    protected static function packageSourcePath(): string
    {
        return __DIR__;
    }
}
