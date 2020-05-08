<?php

namespace Koria\Translatable\Test\Models;

use Koria\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Koria\Translatable\HasColumnTranslations;

class TestModel extends Model implements Translatable
{
    use HasColumnTranslations;

    public array $translatable_keys = [
        'title',
        'name',
        'description'
    ];

    public array $translatable_locales = ['en', 'ru', 'uk'];

    public $timestamps = false;

    protected $table = 'test_models';

    protected $guarded = [];

}
