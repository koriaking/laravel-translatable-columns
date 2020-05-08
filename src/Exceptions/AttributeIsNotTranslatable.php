<?php

namespace Koria\Translatable\Exceptions;

use Exception;
use Koria\Translatable\Translatable;

class AttributeIsNotTranslatable extends Exception
{
    public static function make(string $key, Translatable $model)
    {
        $translatableAttributes = implode(', ', $model->getTranslatableAttributes());

        return new static("Cannot translate attribute `{$key}` as it's not one of the translatable attributes: `$translatableAttributes`");
    }
}
