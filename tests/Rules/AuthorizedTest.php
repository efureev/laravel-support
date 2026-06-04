<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Rules;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Php\Support\Laravel\Rules\Authorized;
use Php\Support\Laravel\Tests\AbstractTestCase;
use Php\Support\Laravel\Tests\Database\Factories\TestModelFactory;
use Php\Support\Laravel\Tests\Database\Factories\UserFactory;
use Php\Support\Laravel\Tests\TestClasses\Models\TestModel;
use Php\Support\Laravel\Tests\TestClasses\Policies\TestModelPolicy;
use PHPUnit\Framework\Attributes\Test;

class AuthorizedTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Gate::policy(TestModel::class, TestModelPolicy::class);
    }

    #[Test]
    public function it_will_return_true_if_the_gate_returns_true_for_the_given_ability_name()
    {
        $rule = new Authorized('edit', TestModel::class);

        $user  = UserFactory::new()->create();
        $model = TestModelFactory::new()->create(
            [
                'user_id' => $user->getKey(),
            ]
        );

        $this->actingAs($user);

        self::assertTrue(Validator::make(['attribute' => $model->getKey()], ['attribute' => $rule])->passes());
    }

    #[Test]
    public function it_will_return_false_if_noone_is_logged_in()
    {
        $rule = new Authorized('edit', TestModel::class);

        $user  = UserFactory::new()->create();
        $model = TestModelFactory::new()->create(
            [
                'user_id' => $user->getKey(),
            ]
        );

        self::assertFalse(Validator::make(['attribute' => $model->getKey()], ['attribute' => $rule])->passes());
    }

    #[Test]
    public function it_will_return_false_if_the_model_is_not_found()
    {
        $rule = new Authorized('edit', TestModel::class);

        self::assertFalse(Validator::make(['attribute' => '2'], ['attribute' => $rule])->passes());
    }

    #[Test]
    public function it_will_return_false_if_the_gate_returns_false()
    {
        $rule = new Authorized('edit', TestModel::class);

        self::assertFalse(Validator::make(['attribute' => '1'], ['attribute' => $rule])->passes());
    }

    #[Test]
    public function it_passes_attribute_ability_and_class_name_to_the_validation_message()
    {
        Lang::addLines(
            ['messages.authorized' => ':attribute :ability and :className'],
            Lang::getLocale(),
            'laravelSupport'
        );

        $rule = new Authorized('edit', TestModel::class);

        $validator = Validator::make(['name_field' => 'John Doe'], ['name_field' => $rule]);

        self::assertFalse($validator->passes());
        self::assertEquals('name_field edit and TestModel', $validator->errors()->first('name_field'));
    }
}
