<?php

namespace Koria\Translatable\Test;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Koria\Translatable\Test\Models\TestModel;
use Koria\Translatable\TranslatableServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @var TestModel */
    protected $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/factories');

        $this->setUpDatabase();

        $this->model = create(TestModel::class);
    }

    protected function setUpDatabase()
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');

            $table->text('title_en')->nullable();
            $table->text('title_ru')->nullable();
            $table->text('title_uk')->nullable();

            $table->text('name_en')->nullable();
            $table->text('name_ru')->nullable();
            $table->text('name_uk')->nullable();

            $table->text('description_en')->nullable();
            $table->text('description_ru')->nullable();
            $table->text('description_uk')->nullable();

            $table->text('field_with_mutator_en')->nullable();
            $table->text('field_with_mutator_ru')->nullable();
            $table->text('field_with_mutator_uk')->nullable();

            $table->text('field_with_accessor_en')->nullable();
            $table->text('field_with_accessor_ru')->nullable();
            $table->text('field_with_accessor_uk')->nullable();
        });
    }

    protected function getPackageProviders($app)
    {
        return [TranslatableServiceProvider::class];
    }

    protected function keys(...$args)
    {
        $this->model->setTranslatableKeys(...$args);
    }

    protected function locales(...$args)
    {
        $this->model->setTranslatableLocales(...$args);
    }

}
