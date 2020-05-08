<?php

namespace Koria\Translatable\Test\Features;

use Koria\Translatable\Test\TestCase;
use Koria\Translatable\Test\Models\TestModel;
use Koria\Translatable\Exceptions\AttributeIsNotTranslatable;

class TranslatableTest extends TestCase
{

    /** @test */
    public function it_will_return_package_fallback_locale_translation_when_getting_an_unknown_locale()
    {
        locales('en', 'ru');

        $this->assertSame($this->model->name_ru, $this->model->getTranslation('name', 'de'));
    }

    /** @test */
    public function it_will_return_default_fallback_locale_translation_when_getting_an_unknown_locale()
    {
        locales('en', null);

        $this->assertSame($this->model->name_en, $this->model->getTranslation('name', 'de'));
    }

    /** @test */
    public function it_provides_a_flog_to_not_return_fallback_locale_translation_when_getting_an_unknown_locale()
    {
        locales('en', null);

        $this->assertSame('', $this->model->getTranslation('name', 'de', false));
    }

    /** @test */
    public function it_will_return_fallback_locale_translation_when_getting_an_unknown_locale_and_fallback_is_true()
    {
        locales('en', null);

        $this->assertSame($this->model->name_en, $this->model->getTranslationWithFallback('name', 'fr'));
    }

    /** @test */
    public function it_will_return_an_empty_string_when_getting_an_unknown_locale_and_fallback_is_not_set()
    {
        locales(null, null);

        $this->assertSame('', $this->model->getTranslationWithoutFallback('name', 'fr'));
    }

    /** @test */
    public function it_will_return_an_empty_string_when_getting_an_unknown_locale_and_fallback_is_empty()
    {
        locales('', '');

        $this->assertSame('', $this->model->getTranslation('name', 'kz'));
    }

    /** @test */
    public function it_can_save_a_translated_attribute()
    {
        $this->model->setTranslation('name', 'en', 'testValue_en');
        $this->model->save();

        $this->assertSame('testValue_en', $this->model->name_en);
    }

    /** @test */
    public function it_can_set_translated_values_when_creating_a_model()
    {
        $this->model = TestModel::create([
            'name' => [
                'en' => 'testValue_en',
                'ru' => 'testValue_ru'
            ],
        ]);

        $this->keys('name');
        $this->locales('en', 'ru');

        $this->assertSame('testValue_en', $this->model->name_en);
        $this->assertSame('testValue_ru', $this->model->name_ru);
    }

    /** @test */
    public function it_can_save_multiple_translations()
    {
        locales('en');
        $this->model->setTranslation('name', 'en', 'testValue_en');
        $this->model->setTranslation('name', 'ru', 'testValue_ru');
        $this->model->save();

        $this->assertSame('testValue_en', $this->model->name);
        $this->assertSame('testValue_ru', $this->model->getTranslation('name', 'ru'));
    }

    /** @test */
    public function it_will_return_the_value_of_the_current_locale_when_using_the_property()
    {
        locales('ru');
        $this->assertSame($this->model->name_ru, $this->model->name);

        locales('en');
        $this->assertSame($this->model->name_en, $this->model->name);

        locales('de', 'ru');
        $this->assertSame($this->model->name_ru, $this->model->name);

        locales('de', 'en');
        $this->assertSame($this->model->name_en, $this->model->name);
    }

    /** @test */
    public function it_can_get_all_translations_in_one_go()
    {
        $this->assertSame([
            'en' => $this->model->name_en,
            'ru' => $this->model->name_ru,
            'uk' => $this->model->name_uk,
        ], $this->model->getTranslations('name'));
    }

    /** @test */
    public function it_can_get_all_translations_for_all_translatable_attributes_in_one_go()
    {
        $this->keys('name', 'description');
        $this->locales('en', 'ru');

        $this->assertSame([
            'name'        => [
                'en' => $this->model->name_en,
                'ru' => $this->model->name_ru,
            ],
            'description' => [
                'en' => $this->model->description_en,
                'ru' => $this->model->description_ru,
            ]
        ], $this->model->getTranslations());
    }

    /** @test */
    public function it_can_get_the_locales_which_have_a_translation()
    {
        $this->keys('name');
        $this->locales('en', 'ru');
        $this->assertSame(['en', 'ru'], $this->model->getTranslatedLocales('name'));
    }

    /** @test */
    public function it_can_forget_a_translation()
    {
        $this->keys('name');
        $this->locales('en', 'ru');

        $this->model->setTranslation('name', 'en', 'testValue_en');
        $this->model->setTranslation('name', 'ru', 'testValue_ru');
        $this->model->save();

        $this->assertSame([
            'en' => 'testValue_en',
            'ru' => 'testValue_ru',
        ], $this->model->getTranslations('name'));

        $this->model->forgetTranslation('name', 'en');
        $this->assertSame([
            'ru' => 'testValue_ru',
        ], $this->model->getTranslations('name'));
    }

    /** @test */
    public function it_can_forget_a_field_with_mutator_translation()
    {
        $this->model = new class() extends TestModel {
            public function setTitleAttribute($value, $locale)
            {
                $this->attributes['title_'.$locale] = $value;
            }
        };

        $this->keys('title');
        $this->locales('en', 'ru');

        $this->model->setTranslation('title', 'en', 'testValue_en');
        $this->model->setTranslation('title', 'ru', 'testValue_ru');
        $this->model->save();

        $this->assertSame([
            'en' => 'testValue_en',
            'ru' => 'testValue_ru',
        ], $this->model->getTranslations('title'));

        $this->model->forgetTranslation('title', 'en');

        $this->assertSame([
            'ru' => 'testValue_ru',
        ], $this->model->getTranslations('title'));
    }

    /** @test */
    public function it_can_forget_all_translations()
    {
        $this->keys('name', 'description', 'title');
        $this->locales('en', 'ru');

        $this->model->setTranslation('name', 'en', 'testValue_en');
        $this->model->setTranslation('name', 'ru', 'testValue_ru');

        $this->model->setTranslation('description', 'en', 'testValue_en');
        $this->model->setTranslation('description', 'ru', 'testValue_ru');

        $this->model->setTranslation('title', 'en', 'testValue_en');
        $this->model->setTranslation('title', 'ru', 'testValue_ru');
        $this->model->save();

        $this->assertSame([
            'en' => 'testValue_en',
            'ru' => 'testValue_ru',
        ], $this->model->getTranslations('name'));

        $this->assertSame([
            'en' => 'testValue_en',
            'ru' => 'testValue_ru',
        ], $this->model->getTranslations('description'));

        $this->assertSame([
            'en' => 'testValue_en',
            'ru' => 'testValue_ru',
        ], $this->model->getTranslations('title'));

        $this->model->forgetAllTranslations('en');

        $this->assertSame([
            'ru' => 'testValue_ru',
        ], $this->model->getTranslations('name'));

        $this->assertSame([
            'ru' => 'testValue_ru',
        ], $this->model->getTranslations('description'));

        $this->assertSame([
            'ru' => 'testValue_ru',
        ], $this->model->getTranslations('title'));
    }

    /** @test */
    public function it_will_throw_an_exception_when_trying_to_translate_an_untranslatable_attribute()
    {
        $this->expectException(AttributeIsNotTranslatable::class);

        $this->model->setTranslation('untranslated', 'en', 'value');
    }

    /** @test */
    public function it_is_compatible_with_accessors_on_non_translatable_attributes()
    {
        $testModel = new class() extends TestModel {
            public function getOtherFieldAttribute(): string
            {
                return 'accessorName';
            }
        };

        $this->assertEquals((new $testModel())->otherField, 'accessorName');
    }

    /** @test */
    public function it_can_use_accessors_on_translated_attributes()
    {
        $model = new class() extends TestModel {
            public function getNameAttribute($value): string
            {
                return "global {$value}";
            }

            public function getNameEnAttribute($value): string
            {
                return "local {$value}";
            }
        };

        $model->setTranslations('name', [
            'ru' => 'testValue_ru',
            'en' => 'testValue_en',
            'uk' => 'testValue_uk',
        ]);

        locales('ru');
        $this->assertEquals("global testValue_ru", $model->name);
        $this->assertEquals("global testValue_ru", $model->getTranslation('name', 'ru'));

        $this->assertEquals("local testValue_en", $model->name_en);
        $this->assertEquals("local testValue_en", $model->getTranslation('name', 'en'));

        $this->assertEquals("global testValue_uk", $model->name_uk);
        $this->assertEquals("global testValue_uk", $model->getTranslation('name', 'uk'));
    }

    /** @test */
    public function it_can_use_mutators_on_translated_attributes()
    {
        $testModel = new class() extends TestModel {
            public function setNameAttribute($value, $locale)
            {
                $this->attributes['name_'.$locale] = "I just mutated {$value}";
            }
        };

        $testModel->setTranslation('name', 'en', 'testValue_en');

        $this->assertEquals($testModel->name, 'I just mutated testValue_en');
    }

    /** @test */
    public function it_can_set_translations_for_default_language()
    {
        $model = TestModel::create([
            'name' => [
                'en' => 'testValue_en',
                'ru' => 'testValue_ru',
            ],
        ]);

        locales('en');

        $model->name = 'updated_en';
        $this->assertEquals('updated_en', $model->name);
        $this->assertEquals('testValue_ru', $model->getTranslation('name', 'ru'));

        locales('ru');

        $model->name = 'updated_ru';
        $this->assertEquals('updated_ru', $model->name);
        $this->assertEquals('updated_en', $model->getTranslation('name', 'en'));
    }

    /** @test */
    public function it_can_set_multiple_translations_at_once()
    {
        $translations = ['en' => 'testValue_en', 'ru' => 'testValue_ru', 'uk' => 'testValue_uk'];

        $this->model->setTranslations('name', $translations);
        $this->model->save();

        $this->assertEquals($translations, $this->model->getTranslations('name'));
    }

    /** @test */
    public function it_can_check_if_an_attribute_is_translatable()
    {
        $this->assertTrue($this->model->isTranslatableAttribute('name'));

        $this->assertFalse($this->model->isTranslatableAttribute('other'));
    }

    /** @test */
    public function it_can_check_if_an_attribute_has_translation()
    {
        $this->model->setTranslation('name', 'en', 'testValue_en');
        $this->model->setTranslation('name', 'ru', null);
        $this->model->save();

        $this->assertTrue($this->model->hasTranslation('name', 'en'));

        $this->assertFalse($this->model->hasTranslation('name', 'pt'));
    }

    /** @test */
    public function it_can_correctly_set_a_field_when_a_mutator_is_defined()
    {
        locales('uk');
        $testModel = (new class() extends TestModel {
            public function setNameAttribute($value)
            {
                $this->attributes['name_'.$this->getLocale()] = "I just mutated {$value}";
            }
        });
        $this->keys('name');
        $this->locales('uk');
        $testModel->name = 'hello';

        $expected = ['uk' => 'I just mutated hello'];
        $this->assertEquals($expected, $testModel->getTranslations('name'));
    }

    /** @test */
    public function it_can_set_multiple_translations_when_a_mutator_is_defined()
    {
        $testModel = (new class() extends TestModel {
            public function setNameAttribute($value, $locale)
            {
                $this->attributes['name_'.$locale] = "I just mutated {$value}";
            }
        });

        $translations = [
            'en' => 'en',
            'ru' => 'ru',
            'uk' => 'uk',
        ];

        $testModel->setTranslations('name', $translations);

        $testModel->save();

        $expected = [
            'en' => 'I just mutated en',
            'ru' => 'I just mutated ru',
            'uk' => 'I just mutated uk',
        ];

        $this->assertEquals($expected, $testModel->getTranslations('name'));
    }

    /** @test */
    public function it_can_translate_a_field_based_on_the_translations_of_another_one()
    {
        $testModel = (new class() extends TestModel {
            public function setTitleAttribute($value, $locale = 'en')
            {
                $this->attributes['title_'.$locale] = $value.' '.$this->getTranslation('name', $locale);
            }
        });

        $this->keys('name', 'title');
        $this->locales('ru', 'en');

        $testModel->setTranslations('name', [
            'ru' => 'name_ru',
            'en' => 'name_en',
        ]);

        $testModel->setTranslations('title', [
            'ru' => 'privet',
            'en' => 'hello',
        ]);

        $testModel->save();

        $expected = [
            'ru' => 'privet name_ru',
            'en' => 'hello name_en',
        ];

        $this->assertEquals($expected, $testModel->getTranslations('title'));
    }

    /** @test */
    public function it_handle_null_value_from_database()
    {
        $testModel = create(TestModel::class, [
            'name_en' => null,
            'name_ru' => null,
            'name_uk' => 'null'
        ]);

        locales('en');

        $this->assertEquals('', $testModel->name);
        $this->assertEquals('', $testModel->getTranslation('name', 'ru'));
        $this->assertEquals('null', $testModel->getTranslation('name', 'uk'));
    }

    /** @test */
    public function it_can_get_all_translations()
    {
        $this->keys('name', 'description', 'title');
        $this->locales('en', 'ru');

        $translations = ['en' => 'hallo', 'ru' => 'hello'];
        $this->model = new TestModel();
        $this->model->setTranslations('name', $translations);
        $this->model->setTranslations('description', $translations);
        $this->model->save();

        $this->assertEquals([
            'title'       => [],
            'name'        => $translations,
            'description' => $translations,
        ], $this->model->translations);
    }

    /** @test */
    public function it_will_return_fallback_locale_translation_when_getting_an_empty_translation_from_the_locale()
    {
        locales('en');

        $this->model->setTranslation('name', 'en', 'testValue_en');
        $this->model->setTranslation('name', 'ru', null);
        $this->model->setTranslation('name', 'uk', '');
        $this->model->save();

        $this->assertSame('testValue_en', $this->model->getTranslation('name', 'ru'));
        $this->assertSame('testValue_en', $this->model->getTranslation('name', 'uk'));
    }

    /** @test */
    public function it_will_return_correct_translation_value_if_value_is_set_to_false_or_true()
    {
        $this->model->setTranslation('name', 'ru', 'false');
        $this->model->setTranslation('description', 'ru', false);
        $this->model->setTranslation('name', 'uk', 'true');
        $this->model->setTranslation('description', 'uk', true);
        $this->model->save();

        $this->assertSame('false', $this->model->getTranslation('name', 'ru'));
        $this->assertSame(false, $this->model->getTranslation('description', 'ru'));
        $this->assertSame('true', $this->model->getTranslation('name', 'uk'));
        $this->assertSame(true, $this->model->getTranslation('description', 'uk'));
    }

    /** @test */
    public function it_will_return_correct_translation_value_if_value_is_set_to_zero()
    {
        $this->model->setTranslation('name', 'ru', '0');
        $this->model->setTranslation('description', 'ru', 0);
        $this->model->save();

        $this->assertSame('0', $this->model->getTranslation('name', 'ru'));
        $this->assertSame(0, $this->model->getTranslation('description', 'ru'));
    }

    /** @test */
    public function it_will_not_return_fallback_value_if_value_is_set_to_zero()
    {
        locales('en');

        $this->model->setTranslation('name', 'ru', '0');
        $this->model->setTranslation('name', 'en', '1');
        $this->model->save();

        $this->assertSame('0', $this->model->getTranslation('name', 'ru'));
        $this->assertSame('1', $this->model->getTranslation('name', 'en'));

        $this->model->setTranslation('name', 'ru', 0);
        $this->model->setTranslation('name', 'en', 1);
        $this->model->save();

        $this->assertSame(0, $this->model->getTranslation('name', 'ru'));
        $this->assertSame(1, $this->model->getTranslation('name', 'en'));
    }
}
