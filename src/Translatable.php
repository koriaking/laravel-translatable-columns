<?php

namespace Koria\Translatable;

interface Translatable
{
    public function getTranslatableAttributes(): array;

    public function getTranslatedLocales(string $key): array;

    public function setTranslatableKeys(...$array): self;

    public function setTranslatableLocales(...$array): self;
}
