<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Php\Support\Laravel\Tests\TestClasses\Entity\Status;
use Php\Support\Laravel\Tests\TestClasses\Models\TestModel;

$factory->define(
    TestModel::class,
    static function (Faker $faker) {
        return [
            'title'   => $faker->sentence,
            'str'     => $faker->title,
            'enabled' => $faker->randomElement([true, false]),
            'status'  => $faker->randomElement(Status::STATUSES),
        ];
    }
);
