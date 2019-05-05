<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use generators\Language\LanguageGenerator;
use generators\Language\enums\LangvarTypes;

final class LanguageGeneratorTest extends TestCase
{
    private $generator;
    private $config;
    private $testFilename = ROOT_DIR . '/tests/sources/po/test.po';
    private $testWhitespacesFilename = ROOT_DIR . '/tests/sources/po/test-whitespace-before.po';
    private $testWhitespacesFilenameResult = ROOT_DIR . '/tests/sources/po/test-whitespace-after.po';

    protected function setUp(): void
    {
        $this->config = new Config([
            'addon.id' => 'sd_addon'
        ],
        [
            'addon.default_language' => 'en',
            'filesystem.output_path_relative' => './'
        ]);

        $this->generator = new LanguageGenerator($this->config);
    }

    /**
     * @covers generators\Language\LanguageGenerator::setEndingNewLine
     */
    public function testNewLine(): void
    {
        $without_new_line = <<<EOD
check file
for new
line
endings
EOD;
        $with_new_line = $without_new_line . PHP_EOL;
        $this->assertEquals(
            explode_by_new_line($with_new_line),
            explode_by_new_line(LanguageGenerator::setEndingNewLine($without_new_line))
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::setContent
     * @covers generators\Language\LanguageGenerator::toString
     */
    public function testSetContent(): void
    {
        $test_content = <<<EOD
msgctxt "Languages::email_marketing.subscription_confirmed"
msgid "Thank you for subscribing to our newsletter"
msgstr "Merci de votre inscription à notre newsletter"

EOD;

        $this->assertEquals(
            explode_by_new_line($test_content),
            explode_by_new_line($this->generator->setContent($test_content)->toString())
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::setContent
     * @covers generators\Language\LanguageGenerator::appendContent
     * @covers generators\Language\LanguageGenerator::toString
     */
    public function testAppendContent(): void
    {
        $test_content = <<<EOD
msgctxt "Languages::email_marketing.subscription_confirmed"
msgid "Thank you for subscribing to our newsletter"
msgstr "Merci de votre inscription à notre newsletter"

msgctxt "Languages::tt_views_block_manager_update_block_width"
msgid "Block width works for blocks located in the group having horizontal direction, for other cases this parameter does not work."
msgstr "Block width works for blocks located in the group having horizontal direction, for other cases this parameter does not work."

EOD;

        $initial_content = <<<EOD
msgctxt "Languages::email_marketing.subscription_confirmed"
msgid "Thank you for subscribing to our newsletter"
msgstr "Merci de votre inscription à notre newsletter"
EOD;
        $appending_content = <<<EOD
msgctxt "Languages::tt_views_block_manager_update_block_width"
msgid "Block width works for blocks located in the group having horizontal direction, for other cases this parameter does not work."
msgstr "Block width works for blocks located in the group having horizontal direction, for other cases this parameter does not work."
EOD;

        $this->assertEquals(
            explode_by_new_line($test_content),
            explode_by_new_line(
                $this->generator
                    ->setContent($initial_content)
                    ->appendContent($appending_content)
                    ->toString()
                )
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::getTranslationKey
     */
    public function testGetTranslationKey(): void
    {
        $this->assertSame(
            LangvarTypes::$LANGUAGES . '::email_marketing.subscription_confirmed',
            LanguageGenerator::getTranslationKey(
                LangvarTypes::$LANGUAGES,
                'email_marketing',
                'subscription_confirmed'
            )
        );

        $this->assertSame(
            LangvarTypes::$SETTINGS_OPTIONS . '::special_jobs::special_request_title',
            LanguageGenerator::getTranslationKey(
                LangvarTypes::$SETTINGS_OPTIONS,
                'special_jobs',
                'special_request_title'
            )
        );

        $this->assertSame(
            LangvarTypes::$LANGUAGES . '::payments.epdq.tbl_bgcolor',
            LanguageGenerator::getTranslationKey(
                LangvarTypes::$LANGUAGES,
                'payments.epdq',
                'tbl_bgcolor'
            )
        );

        $this->assertSame(
            LangvarTypes::$SETTINGS_SECTIONS . '::sample_addon_3_0::section1',
            LanguageGenerator::getTranslationKey(
                LangvarTypes::$SETTINGS_SECTIONS,
                'sample_addon_3_0',
                'section1'
            )
        );

        $this->assertSame(
            LangvarTypes::$SETTINGS_TOOLTIPS . '::sample_addon_3_0::input',
            LanguageGenerator::getTranslationKey(
                LangvarTypes::$SETTINGS_TOOLTIPS,
                'sample_addon_3_0',
                'input'
            )
        );

        $this->assertSame(
            LangvarTypes::$SETTINGS_VARIANTS . '::sample_addon_3_0::radiogroup::radio_2',
            LanguageGenerator::getTranslationKey(
                LangvarTypes::$SETTINGS_VARIANTS,
                'sample_addon_3_0',
                'radiogroup',
                'radio_2'
            )
        );

        $this->assertSame(
            LangvarTypes::$PROFILE_FIELDS . '::email',
            LanguageGenerator::getTranslationKey(
                LangvarTypes::$PROFILE_FIELDS,
                '',
                'email'
            )
        );

        $this->assertSame(
            LangvarTypes::$ADDONS . '::name::sample_addon_3_0',
            LanguageGenerator::getTranslationKey(
                LangvarTypes::$ADDONS,
                'name',
                'sample_addon_3_0'
            )
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::findByKey
     */
    public function testFindByKey(): void
    {
        $test_content = file_get_contents($this->testFilename);

        $generator = new LanguageGenerator($this->config);

        $this->assertSame(
            [
                'msgctxt'   => 'Languages::payments.epdq.tbl_bgcolor',
                'msgid'     => 'Table background color',
                'msgstr'    => 'Table background color',
            ],
            $generator->setContent($test_content)->findByKey(
                LanguageGenerator::getTranslationKey(LangvarTypes::$LANGUAGES, 'payments.epdq', 'tbl_bgcolor')
            )
        );

        $this->assertSame(
            false,
            $generator->findByKey(
                LanguageGenerator::getTranslationKey(LangvarTypes::$LANGUAGES, 'not_found', 'test_cannot_find')
            )
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::removeByKey
     * @covers generators\Language\LanguageGenerator::setContent
     * @covers generators\Language\LanguageGenerator::getTranslationKey
     */
    public function testRemove(): void
    {
        $test_content = <<<EOD
msgctxt "Languages::tts_generate_submenu"
msgid "The submenu will include child elements of the selected object."
msgstr "The submenu will include child elements of the selected object."

msgctxt "SettingsVariants::sign_in_default_action::checkout_as_guest"
msgid "Checkout as guest"
msgstr "Checkout as guest"
EOD;
        $generator = new LanguageGenerator($this->config);

        $generator->setContent($test_content)
            ->removeByKey(
                LanguageGenerator::getTranslationKey(
                    LangvarTypes::$LANGUAGES,
                    '',
                    'tts_generate_submenu'
                )
            )
            ->removeByKey(
                LanguageGenerator::getTranslationKey(
                    LangvarTypes::$SETTINGS_VARIANTS,
                    '',
                    'sign_in_default_action',
                    'checkout_as_guest'
                )
            );

        $this->assertSame(
            '',
            $generator->toString()
        );

        $this->assertSame(
            $generator::purify($test_content),
            $generator->getRecycleBin()
        );

        unset($generator);
        unset($test_content);

        // new test
        $test_content = 'msgctxt "Languages::tts_generate_submenu"'
            . "\r\n" . 'msgid "The submenu will include child elements of the selected object."'
            . "\r\n" . 'msgstr "The submenu will include child elements of the selected object."';

        $generator = new LanguageGenerator($this->config);

        $generator->setContent($test_content)->removeByKey(
            LanguageGenerator::getTranslationKey(
                LangvarTypes::$LANGUAGES,
                '',
                'tts_generate_submenu'
            )
        );

        $this->assertSame(
            '',
            $generator->toString()
        );

        unset($generator);
        unset($test_content);

        // new test
        $test_content = file_get_contents($this->testFilename);
        $generator = new LanguageGenerator($this->config);

        $generator->setContent($test_content);

        // exists before removing
        $this->assertSame(
            [
                'msgctxt'   => 'ProfileFields::email',
                'msgid'     => 'E-mail',
                'msgstr'    => 'E-mail',
            ],
            $generator->findByKey(
                LanguageGenerator::getTranslationKey(
                    LangvarTypes::$PROFILE_FIELDS,
                    '',
                    'email'
                )
            )
        );

        // removing
        $generator->removeByKey(
            LanguageGenerator::getTranslationKey(
                LangvarTypes::$PROFILE_FIELDS,
                '',
                'email'
            )
        );

        // not exists after removing
        $this->assertSame(
            false,
            $generator->findByKey(
                LanguageGenerator::getTranslationKey(
                    LangvarTypes::$PROFILE_FIELDS,
                    '',
                    'email'
                )
            )
        );
    }

    public function testRemoveById(): void
    {
        $test_content = file_get_contents($this->testFilename);

        $generator = new LanguageGenerator($this->config);
        $generator->setContent($test_content);

        $generator->removeById('name');

        $this->assertSame(
            false,
            $generator->findByKey(
                LanguageGenerator::getTranslationKey(
                    LangvarTypes::$SETTINGS_VARIANTS,
                    'sd_addon',
                    'name',
                    'Jane'
                )
            )
        );

        $this->assertSame(
            false,
            $generator->findByKey(
                LanguageGenerator::getTranslationKey(
                    LangvarTypes::$SETTINGS_VARIANTS,
                    'sd_addon',
                    'name',
                    'Isaac'
                )
            )
        );

        $this->assertSame(
            false,
            $generator->findByKey(
                LanguageGenerator::getTranslationKey(
                    LangvarTypes::$SETTINGS_TOOLTIPS,
                    'sd_addon',
                    'name'
                )
            )
        );

        $this->assertSame(
            false,
            $generator->findByKey(
                LanguageGenerator::getTranslationKey(
                    LangvarTypes::$SETTINGS_OPTIONS,
                    'sd_addon',
                    'name'
                )
            )
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::create
     * @covers generators\Language\LanguageGenerator::toString
     */
    public function testCreate(): void
    {
        $heading = <<<EOD
msgid ""
msgstr ""
"Language: en\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Pack-Name: English\\n"
"Lang-Code: en\\n"
"Country-Code: US\\n"

EOD;
        $generator = new LanguageGenerator($this->config);

        $this->assertEquals(
            explode_by_new_line($heading),
            explode_by_new_line($generator->create()->toString())
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::replaceLangvar
     * @covers generators\Language\LanguageGenerator::toString
     * @covers generators\Language\LanguageGenerator::getTranslationKey
     */
    public function testReplaceLangvar()
    {
        $generator = new LanguageGenerator($this->config);
        $test_content = <<<EOD
msgctxt "Languages::tts_generate_submenu"
msgid "The submenu will include child elements of the selected object"
msgstr "The submenu will include child elements of the selected object"

EOD;
        
        $generator->replaceLangvar(
            LanguageGenerator::getTranslationKey(LangvarTypes::$LANGUAGES, '', 'tts_generate_submenu'),
            'The submenu will include child elements of the selected object',
            'The submenu will include child elements of the selected object'
        );
        
        $generator->replaceLangvar(
            LanguageGenerator::getTranslationKey(LangvarTypes::$LANGUAGES, '', 'tts_generate_submenu'),
            'The submenu will include child elements of the selected object',
            'The submenu will include child elements of the selected object'
        );

        $this->assertEquals(
            explode_by_new_line($test_content),
            explode_by_new_line($generator->toString())
        );

        unset($generator);
        unset($test_content);

        $test_content = file_get_contents($this->testFilename);

        $generator = new LanguageGenerator($this->config);
        $generator->setContent($test_content);

        $generator->replaceLangvar(
            LanguageGenerator::getTranslationKey(LangvarTypes::$LANGUAGES, 'sd_miltomil', 'not_confirmed'),
            'Your email already confirmed or confirmation code is wrong!'
        );

        $this->assertSame(
            [
                'msgctxt'   => 'Languages::sd_miltomil.not_confirmed',
                'msgid'     => 'Your email already confirmed or confirmation code is wrong!',
                'msgstr'    => 'Your email already confirmed or confirmation code is wrong!',
            ],
            $generator->findByKey(
                LanguageGenerator::getTranslationKey(LangvarTypes::$LANGUAGES, 'sd_miltomil', 'not_confirmed')
            )
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::checkLanguageSupport
     */
    public function testCheckLanguageSupport(): void
    {
        $this->assertSame(
            true,
            LanguageGenerator::checkLanguageSupport('en')
        );

        $this->assertSame(
            true,
            LanguageGenerator::checkLanguageSupport('ru')
        );

        $this->assertSame(
            false,
            LanguageGenerator::checkLanguageSupport('aa')
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::getPath
     */
    public function testGetPath(): void
    {
        $this->assertSame(
            sanitize_filename(ROOT_DIR . $this->config->get('filesystem.output_path_relative') . 'var/langs/en/addons/sd_addon.po'),
            $this->generator->getPath()
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::checkForEdited
     */
    public function testCheckForEdited(): void
    {
        $this->assertSame(
            false,
            $this->generator::checkForEdited('SettingsOptions::sd_addon::name', 'Name')
        );

        $this->assertSame(
            true,
            $this->generator::checkForEdited('SettingsOptions::sd_addon::name', 'NAME')
        );

        $this->assertSame(
            true,
            $this->generator::checkForEdited('SettingsOptions::sd_addon::name', 'Vendor name')
        );

        $this->assertSame(
            false,
            $this->generator::checkForEdited('SettingsVariants::sd_addon::name::Mikle', 'Mikle')
        );

        $this->assertSame(
            true,
            $this->generator::checkForEdited('SettingsVariants::sd_addon::name::Jane', 'Jane Green')
        );

        $this->assertSame(
            false,
            $this->generator::checkForEdited('SettingsSections::test_addon::new_section', 'New section')
        );

        $this->assertSame(
            true,
            $this->generator::checkForEdited('SettingsSections::test_addon::new_section', 'General')
        );
    }

    /**
     * @covers generators\Language\LanguageGenerator::clearWhitespaces
     */
    public function testClearWhitespaces(): void
    {
        $generator              = new LanguageGenerator($this->config);
        $test_content           = file_get_contents($this->testWhitespacesFilename);
        $test_content_result    = file_get_contents($this->testWhitespacesFilenameResult);

        $this->assertSame(
            $generator::replaceEol($test_content_result),
            $generator::clearWhitespaces(
                $generator::replaceEol($test_content)
            )
        );
    }
}
