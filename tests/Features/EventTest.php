<?php

namespace Koria\Translatable\Test\Features;

use Koria\Translatable\Test\TestCase;
use Koria\Translatable\Events\TranslationHasBeenSet;

class EventTest extends TestCase
{
    /** @test */
    public function it_will_fire_an_event_when_a_translation_has_been_set()
    {
        $this->expectsEvents(TranslationHasBeenSet::class);

        $this->model->setTranslation('name', 'en', 'testValue_en');
    }
}
