<?php

namespace Koria\Translatable;

use Illuminate\Support\Str;
use Koria\Translatable\Events\TranslationHasBeenSet;
use Koria\Translatable\Exceptions\AttributeIsNotTranslatable;

/**
 * Trait HasColumnTranslations
 * @package Koria\Translatable
 */
trait HasColumnTranslations
{

    /**
     * @param $key
     * @return mixed|string
     */
    public function getAttribute($key)
    {
        if ($this->isTranslatableLocaleAttribute($key)) {
            return $this->getTranslation(substr($key, 0, -3), substr($key, -2));
        }

        if (!$this->isTranslatableAttribute($key)) {
            return parent::getAttribute($key);
        }

        return $this->getTranslation($key, $this->getLocale());
    }

    /**
     * @param  string  $key
     * @return bool
     */
    public function isTranslatableLocaleAttribute(string $key)
    {
        return $this->isTranslatableAttribute($key = substr($key, 0, -3));
    }

    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes());
    }

    public function getTranslatableAttributes(): array
    {
        return is_array($this->translatable_keys)
            ? $this->translatable_keys
            : [];
    }

    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true)
    {
        $locale = $this->normalizeLocale($key, $locale, $useFallbackLocale);

        $localizedKey = $this->getLocalizedKey($key, $locale);
        $translation = $this->getAttributes()[$localizedKey] ?? '';

        if ($this->hasGetMutator($localizedKey)) {
            return $this->mutateAttribute($localizedKey, $translation);
        } elseif ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $translation);
        }

        if (!$useFallbackLocale) {
            return $translation;
        }

        if (null !== $translation && '' !== $translation) {
            return $translation;
        }

        return $this->getTranslation($key, $this->getLocale(), false);
    }

    protected function normalizeLocale(string $key, string $locale, bool $useFallbackLocale): string
    {
        if (in_array($locale, $this->getTranslatedLocales($key))) {
            return $locale;
        }

        if (!$useFallbackLocale) {
            return $locale;
        }

        if (!is_null($fallbackLocale = config('translatable.fallback_locale'))) {
            return $fallbackLocale;
        }

        if (!is_null($fallbackLocale = config('app.fallback_locale'))) {
            return $fallbackLocale;
        }

        return $locale;
    }

    public function getTranslatedLocales(string $key): array
    {
        return is_array($this->translatable_locales)
            ? $this->translatable_locales
            : [];
    }

    public function getLocalizedKey(string $key, string $locale): string
    {
        return implode('_', [$key, $locale]);
    }

    protected function getLocale(): string
    {
        return app()->getLocale() ?? '';
    }

    public function setAttribute($key, $value)
    {
        // Pass untranslatable attributes to the parent method.
        if (!$this->isTranslatableAttribute($key)) {
            return parent::setAttribute($key, $value);
        }

        if (is_array($value)) {
            foreach ($value as $locale => $data) {
                $this->setTranslation($key, $locale, $data);
            }
            return $this;
        }

        // If the attribute is translatable and not already translated, set a
        // translation for the current app locale.
        return $this->setTranslation($key, $this->getLocale(), $value);
    }

    public function setTranslation(string $key, string $locale, $value): self
    {
        $this->guardAgainstNonTranslatableAttribute($key);

        $localizedKey = $this->getLocalizedKey($key, $locale);

        $oldValue = $translations[$locale] ?? '';

        if ($this->hasSetMutator($localizedKey)) {
            $method = 'set'.Str::studly($localizedKey).'Attribute';

            $this->{$method}($value, $locale);

            $value = $this->attributes[$localizedKey] ?? '';
        }

        if ($this->hasSetMutator($key)) {
            $method = 'set'.Str::studly($key).'Attribute';

            $this->{$method}($value, $locale);

            $value = $this->attributes[$localizedKey] ?? '';
        }

        $this->attributes[$localizedKey] = $value;

        event(new TranslationHasBeenSet($this, $key, $locale, $oldValue, $value));

        return $this;
    }

    protected function guardAgainstNonTranslatableAttribute(string $key)
    {
        if (!$this->isTranslatableAttribute($key)) {
            throw AttributeIsNotTranslatable::make($key, $this);
        }
    }

    public function translate(string $key, string $locale = '', bool $useFallbackLocale = true): string
    {
        return $this->getTranslation($key, $locale, $useFallbackLocale);
    }

    public function getTranslationWithFallback(string $key, string $locale): string
    {
        return $this->getTranslation($key, $locale, true);
    }

    public function getTranslationWithoutFallback(string $key, string $locale)
    {
        return $this->getTranslation($key, $locale, false);
    }

    public function forgetAllTranslations(string $locale): self
    {
        collect($this->getTranslatableAttributes())->each(function (string $attribute) use ($locale) {
            $this->forgetTranslation($attribute, $locale);
        });

        return $this;
    }

    public function forgetTranslation(string $key, string $locale): self
    {
        $key = $this->getLocalizedKey($key, $locale);
        $this->$key = null;

        return $this;
    }

    public function setTranslations(string $key, array $translations): self
    {
        $this->guardAgainstNonTranslatableAttribute($key);

        foreach ($translations as $locale => $translation) {
            $this->setTranslation($key, $locale, $translation);
        }

        return $this;
    }

    public function hasTranslation(string $key, string $locale = null): bool
    {
        $locale = $locale ?: $this->getLocale();

        return isset($this->getTranslations($key)[$locale]);
    }

    public function getTranslations(string $key = null): array
    {
        if (null !== $key) {
            $this->guardAgainstNonTranslatableAttribute($key);

            return array_reduce($this->getTranslatedLocales($key), function ($result, $attribute_key) use ($key) {
                    $translation = $this->getTranslation($key, $attribute_key, false);

                    if (null !== $translation && '' !== $translation) {
                        $result[$attribute_key] = $translation;
                    }

                    return $result;
                }) ?? [];
        }

        return array_reduce($this->getTranslatableAttributes(), function ($result, $attribute_key) {
                $result[$attribute_key] = $this->getTranslations($attribute_key);
                return $result;
            }) ?? [];
    }

    public function getTranslationsAttribute(): array
    {
        return $this->getTranslations();
    }

    public function setTranslatableKeys(...$array): self
    {
        $this->translatable_keys = is_array($array[0]) ? $array[0] : $array;

        return $this;
    }

    public function setTranslatableLocales(...$array): self
    {
        $this->translatable_locales = is_array($array[0]) ? $array[0] : $array;
        return $this;
    }
}
