<?php

namespace Koria\Translatable\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TranslationHasBeenSet
 * @package Koria\Translatable\Events
 */
class TranslationHasBeenSet
{
    /** @var Model */
    public Model $model;

    /** @var string */
    public string $key;

    /** @var string */
    public string $locale;

    /** @var string|null|mixed */
    public $oldValue;

    /** @var string|null|mixed */
    public $newValue;

    /**
     * TranslationHasBeenSet constructor.
     * @param  Model  $model
     * @param  string  $key
     * @param  string  $locale
     * @param $oldValue
     * @param $newValue
     */
    public function __construct(Model $model, string $key, string $locale, $oldValue, $newValue)
    {
        $this->model = $model;
        $this->key = $key;
        $this->locale = $locale;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }
}
