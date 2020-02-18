<?php

/** @var Factory $factory */

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;

$factory->define(
    User::class,
    static function (Faker\Generator $faker) {
        static $password;

        return [
            'name'           => $faker->name,
            'email'          => $faker->unique()->safeEmail,
            'password'       => $password ?: $password = bcrypt('secret'),
            'remember_token' => Str::random(10),
        ];
    }
);
