<?php

namespace generators\Language;

use Config;
use generators\Language\enums\LangvarTypes;
use generators\Language\exceptions\DuplicateException;
use mediators\AbstractMediator;

/**
  * @property string $pathTemplate
  * @property string $path
  * @property string $content
  * @property Config $config
  * @property array $codes
  * @todo add all $codes supported by cs-cart
  */
final class LanguageGenerator extends \generators\AbstractGenerator
{
    // readonly
    private $pathTemplate = 'var/langs/${lang}/addons/${addon}.po';
    private $templatePath = '';
    private $path;
    private $content = '';
    private $config;
    private $mediator;
    private static $codes = [
        'en' => ['pack-name' => 'English', 'country-code' => 'US'],
        'ru' => ['pack-name' => 'Russian', 'country-code' => 'RU']
    ];

    function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getTemplateFilename(): string
    {
        return $this->templatePath;
    }

    public function setMediator(AbstractMediator $mediator): void
    {
        $this->mediator = $mediator;
    }

    public function getPath(): string
    {
        $addon_id = $this->config->getOr('addon', 'addon.id');

        if (!$addon_id) {
            throw new \InvalidArgumentException('Addon id (name) not specified');
        }

        $path = $this->config->get('path')
            . $this->config->get('filesystem.output_path_relative')
            . str_replace(
                [
                    '${lang}',
                    '${addon}'
                ],
                [
                    $this->config->getOr('lang', 'addon.default_language'),
                    $addon_id
                ],
                $this->pathTemplate
            );

        return get_absolute_path($path);
    }

    /**
     * Check language for support
     * @param string $language
     *
     * @return bool
     */
    public static function checkLanguageSupport(string $language): bool
    {
        return array_key_exists($language, self::$codes);
    }

    /**
     * @inheritdoc
     *
     * @return LanguageGenerator
     */
    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Append content to current generator content
     * @param string $content - content to append
     *
     * @return LanguageGenerator
     */
    public function appendContent(string $content)
    {
        $this->setContent(
            (empty($this->content) ? '' : self::setEndingNewLine($this->content) . PHP_EOL)
            . $content
        );

        return $this;
    }

    /**
     * create po heading structure
     * @throws \InvalidArgumentException if nor language param and addon default_language are specified
     *
     * @return LanguageGenerator
     */
    public function create()
    {
        $po_heading_template = <<<'EOD'
msgid ""
msgstr ""
"Language: ${code}\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Pack-Name: ${pack-name}\n"
"Lang-Code: ${code}\n"
"Country-Code: ${country-code}\n"
EOD;

        $language_code          = $this->config->getOr('language', 'addon.default_language');

        if (!$language_code) {
            throw new \InvalidArgumentException('Nor language param and addon default_language are specified');
        }

        $language_information   = self::$codes[$language_code];
        $po_heading = str_replace(
            [
                '${code}',
                '${pack-name}',
                '${country-code}'
            ],
            [
                $language_code,
                $language_information['pack-name'],
                $language_information['country-code']
            ],
            $po_heading_template
        );

        $this->content = $po_heading;

        return $this;
    }

    /**
     * Constructs langvar full key code
     * @param string $type - example: Languages
     * @param string $arguments - parts of path for generating msgxtxt key
     * @todo validate subpath for containing only [a-z_\.]/i - throw exception if not - write tests
     *
     * @return string - return langvar string like Languages::email_marketing.subscription_confirmed
     */
    public static function getTranslationKey(string $type, ...$arguments): string
    {
        return self::getKeyGenerator($type)::generate(...$arguments);
    }

    private static function getKeyGenerator(string $type)
    {
        return '\\generators\\Language\\keyGenerators\\' . $type;
    }

    /**
     * Get langvar array from content
     * @param string $full_key - key for search like Languages::email_marketing.subscription_confirmed
     * @throws \InvalidArgumentException if $full_key is empty
     *
     * @return bool|array - [
     *  'msgctxt' => "Languages::payments.epdq.tbl_bgcolor",
     *  'msgid' =>  "Table background color",
     *  'msgstr' => "Table background color"
     * ]
     */
    public function findByKey(string $full_key)
    {
        if (!$full_key) {
            throw new \InvalidArgumentException('full_key cannot be empty');
        }

        $found_count = preg_match_all(
            '/(msgctxt\s+"(' . $full_key . ')")[\r\n|\n|\r]+(msgid\s+"(.*)")[\r\n|\n|\r]+(msgstr\s+"(.*)")/umi',
            $this->content,
            $matches
        );

        if ($found_count === 0 || $found_count === false) {
            return false;
        }

        return [
            'msgctxt' => $matches[2][0],
            'msgid' => $matches[4][0],
            'msgstr' => $matches[6][0]
        ];
    }

    /**
     * Fully remove langvar, that matches msgctxt (full_key)
     * @param string $full_key - msgctxt
     * @throws \InvalidArgumentException if $full_key is empty
     *
     * @return LanguageGenerator
     */
    public function removeByKey(string $full_key)
    {
        if (!$full_key) {
            throw new \InvalidArgumentException('full_key cannot be empty');
        }

        $new_content = preg_replace(
            '/(msgctxt\s+"' . $full_key . '"[\r\n|\n|\r]+msgid\s+".*"[\r\n|\n|\r]+msgstr\s+".*")([\r\n|\n|\r]*)/umi',
            '',
            $this->content
        );

        return $this->setContent($new_content);
    }

    /**
     * Check for ending line and add it if not found
     * @param string $content - multiline content
     *
     * @return string - multiline content with trailing new line
     */
    public static function setEndingNewLine(string $content): string
    {
        $output_arr = explode_by_new_line($content);

        if (!empty(end($output_arr))) {
            $output_arr[] = '';
        }

        return implode(PHP_EOL, $output_arr);
    }

    /**
     * The file must end with an empty line.
     * @inheritdoc
     */
    public function toString(): string
    {
        return self::setEndingNewLine($this->content);
    }

    /**
     * replace langvar if already exists with same msgctxt
     * and create new if not
     * @todo add langvar right after removed
     * @param string $msgctxt
     * @param string $msgid
     * @param string msgstr - optional, gets value of $msgid if empty
     *
     * @return LanguageGenerator
     */
    public function replaceLangvar(string $msgctxt, string $msgid, string $msgstr = '')
    {
        if (empty($msgctxt)) {
            throw new \InvalidArgumentException('msgctxt cannot be empty');
        }

        $msgstr_actual = $msgstr ?: $msgid;

        $langvar_lines = [
            "msgctxt \"$msgctxt\"",
            "msgid \"$msgid\"",
            "msgstr \"$msgstr_actual\""
        ];

        $this->removeByKey($msgctxt);

        return $this->appendContent(implode(PHP_EOL, $langvar_lines));
    }

    /**
     * @todo realize function and write tests
     * add langvar
     * @param string $msgctxt
     * @param string $msgid
     * @param string msgstr - optional, gets value of $msgid if empty
     * @throws DuplicateException if langvar with such msgctxt already exists
     *
     * @return LanguageGenerator
     */
    public function addLangvar(string $msgctxt, string $msgid, string $msgstr = '')
    {
        if ($this->findByKey($msgctxt)) {
            throw new DuplicateException('langvar with same msgctxt already exists: ' . $msgctxt);
        }

        $this->replaceLangvar($msgctxt, $msgid, $msgstr);

        return $this;
    }

    /**
     * @todo
     * add $this->defaultLanguageContent - this property may contents default language values like en/addons/addon.po
     * For what? we can duplicate it by $this->create() with Ru language then $this->duplicateFromDefault()
     * and now we have ru/addons/addon.po with same structure
     *
     * to be realized
     * $this->setDefaultContent(string $content): LanguageGenerator
     * $this->getDefaultContent(): string - results without po heading (which creates by $this->create()) - it can be cutted on setDefaultContent
     * $this->replaceLangvarIfNotEmpty(string $msgctxt, string $msgid, string $msgstr = ''): LanguageGenerator - replaceLangvar analog,
     *    but not fires if new msgid is empty
     *
     * $this->duplicateFromDefaut(): LanguageGenerator
     *
     * So instead of Generator->create()->addLangvar()->...->toString()
     * should be Generator->create()->setDefaultContent($file_content)->duplicateFromDefault()->toString()
     * now we have a copy of default language file and we can translate it
     *
     */
}
