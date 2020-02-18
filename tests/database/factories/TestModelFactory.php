<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    \Php\Support\Laravel\Tests\TestClasses\Models\TestModel::class,
    static function (Faker $faker) {
        return [
            'title'   => $faker->sentence,
            'str'     => $faker->title,
            'enabled' => $faker->randomElement([true, false]),
        ];
    }
);
