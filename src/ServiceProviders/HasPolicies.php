<?php

declare(strict_types=1);

namespace Php\Support\Laravel\ServiceProviders;

use Illuminate\Support\Facades\Gate;

trait HasPolicies
{
    /** @var array<class-string,class-string> Model::class => Policy::class> */
    protected static array $policies = [];

    protected function registerPolicies(): static
    {
        return $this->registerPoliciesForce(static::$policies);
    }

    /**
     * @param array<class-string,class-string> $policies Model::class => Policy::class
     */
    protected function registerPoliciesForce(array $policies): static
    {
        foreach ($policies as $modelForPolicy => $policy) {
            static::registerPolicy($modelForPolicy, $policy);
        }

        return $this;
    }

    protected static function registerPolicy(string $model, string $policy): void
    {
        Gate::policy($model, $policy);
    }
}
