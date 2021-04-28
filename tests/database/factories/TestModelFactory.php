<?php


namespace Php\Support\Laravel\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Php\Support\Laravel\Tests\TestClasses\Entity\Status;
use Php\Support\Laravel\Tests\TestClasses\Models\TestModel;

class TestModelFactory extends Factory
{
    protected $model = TestModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title'   => $this->faker->sentence,
            'str'     => $this->faker->title,
            'enabled' => $this->faker->randomElement([true, false]),
            'status'  => $this->faker->randomElement(Status::STATUSES),
        ];
    }
}
