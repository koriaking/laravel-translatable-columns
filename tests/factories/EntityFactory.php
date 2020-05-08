<?php

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Koria\Translatable\Test\Models\TestModel;

/** @var Factory $factory */
$factory->define(TestModel::class, function (Faker $faker) {
    foreach (['title', 'name', 'description', 'field_with_mutator', 'field_with_accessor'] as $attribute) {
        foreach (['en', 'ru', 'uk'] as $locale) {
            $data[$attribute.'_'.$locale] = $locale.'_'.$faker->sentence(3);
        }
    }
    return $data ?? [];
});
