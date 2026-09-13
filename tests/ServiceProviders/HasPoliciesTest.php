<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\ServiceProviders;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Gate;
use Php\Support\Laravel\ServiceProviders\AbstractServiceProvider;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\TestClasses\Models\TestModel;
use Php\Support\Laravel\Tests\TestClasses\Policies\TestModelPolicy;
use PHPUnit\Framework\Attributes\Test;

class HasPoliciesTest extends AbstractTestCase
{
    #[Test]
    public function declared_policies_are_registered_with_the_gate(): void
    {
        (new PolicyProvider($this->app))->registerDeclaredPolicies();

        self::assertInstanceOf(TestModelPolicy::class, Gate::getPolicyFor(TestModel::class));
    }

    #[Test]
    public function policies_can_be_registered_ad_hoc(): void
    {
        (new PolicyProvider($this->app))->registerExtraPolicies([TestModel::class => TestModelPolicy::class]);

        self::assertInstanceOf(TestModelPolicy::class, Gate::getPolicyFor(TestModel::class));
    }

    #[Test]
    public function registering_policies_is_chainable(): void
    {
        $provider = new PolicyProvider($this->app);

        self::assertSame($provider, $provider->registerDeclaredPolicies());
    }
}

class NoopCommand extends Command
{
    protected $signature = 'example:noop';

    protected $description = 'Does nothing.';
}

class SecondNoopCommand extends Command
{
    protected $signature = 'example:noop-2';

    protected $description = 'Does nothing either.';
}

class PolicyProvider extends AbstractServiceProvider
{
    protected static array $policies = [
        TestModel::class => TestModelPolicy::class,
    ];

    protected static array $commands = [
        NoopCommand::class,
    ];

    public function registerDeclaredPolicies(): static
    {
        return $this->registerPolicies();
    }

    /**
     * @param array<class-string, class-string> $policies
     */
    public function registerExtraPolicies(array $policies): static
    {
        return $this->registerPoliciesForce($policies);
    }

    public function registerDeclaredCommands(): static
    {
        return $this->registerCommands();
    }

    /**
     * @param class-string[] $commands
     */
    public function registerExtraCommands(array $commands): static
    {
        return $this->registerCommandsForce($commands);
    }

    protected static function packageSourcePath(): string
    {
        return __DIR__;
    }
}
