<?php

namespace Php\Support\Laravel\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Php\Support\Laravel\Tests\TestClasses\Models\TestModel;

/**
 * @extends Factory<TestModel>
 */
class TestModelFactory extends Factory
{
    protected $model = TestModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'   => $this->faker->sentence,
            'str'     => $this->faker->title,
            'enabled' => $this->faker->randomElement([true, false]),
        ];
    }
}
