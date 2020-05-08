<?php

use Koria\Translatable\Test\Models\TestModel;

function create($class, $attributes = [], $amount = null): TestModel
{
    return factory($class, $amount)->create($attributes);
}

function make($class, $attributes = [], $amount = null): TestModel
{
    return factory($class, $amount)->make($attributes);
}

function locales($app = 'en', $package = null)
{
    app()['config']->set('app.fallback_locale', $app);
    app()['config']->set('translatable.fallback_locale', $package);
    app()->setLocale($app);
}
