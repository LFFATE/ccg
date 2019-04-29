<?php

namespace generators\Language;

use Config;
use generators\Language\exceptions\DuplicateException;
use mediators\AbstractMediator;

/**
  * @property string $pathTemplate
  * @property string $templatePath
  * @property string $recycleBin - buffer to which be removed all langvars from actual content
  * @property string $content
  * @property Config $config
  * @property AbstractMediator $mediator
  * @property array $codes
  * @property string $eol - end of line char
  * @todo add all $codes supported by cs-cart
  */
final class LanguageGenerator extends \generators\AbstractGenerator
{
    // readonly
    private $pathTemplate = 'var/langs/${lang}/addons/${addon}.po';
    private $templatePath = '';
    private $recycleBin = '';
    private $content = '';
    private $config;
    private $mediator;
    private static $codes = [
        'en' => ['pack-name' => 'English', 'country-code' => 'US'],
        'ru' => ['pack-name' => 'Russian', 'country-code' => 'RU']
    ];
    private static $eol = "\n";

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

        return sanitize_filename($path);
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
     * Replaces different style eol by one
     * 
     * @param string $content - content wich will be changed
     * 
     * @return string - content with one-style eol
     */
    public static function replaceEol(string $content): string
    {
        return preg_replace('~\r\n?~', self::$eol, $content);
    }

    /**
     * @inheritdoc
     *
     * @return LanguageGenerator
     */
    public function setContent(string $content)
    {
        $this->content = self::replaceEol($content);

        return $this;
    }

    /**
     * get recycleBin
     * 
     * @return string
     */
    public function getRecycleBin(): string
    {
        return self::purify($this->recycleBin);
    }

    /**
     * Set content to recycleBin
     *
     * @return LanguageGenerator
     */
    public function setRecycleBin(string $content)
    {
        $this->recycleBin = self::replaceEol($content);

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
     * Append content to recycleBin
     *
     * @return LanguageGenerator
     */
    public function appendRecycleBin(string $content)
    {
        $this->setRecycleBin(
            (empty($this->recycleBin) ? '' : self::setEndingNewLine($this->recycleBin) . PHP_EOL)
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
        return self::findByKeyIn($full_key, $this->content);
    }

    /**
     * Get langvar array from recycleBin
     * @param string $full_key - key for search like Languages::email_marketing.subscription_confirmed
     * @throws \InvalidArgumentException if $full_key is empty
     *
     * @return bool|array - [
     *  'msgctxt' => "Languages::payments.epdq.tbl_bgcolor",
     *  'msgid' =>  "Table background color",
     *  'msgstr' => "Table background color"
     * ]
     */
    public function findByKeyInRecycleBin(string $full_key)
    {
        return self::findByKeyIn($full_key, $this->recycleBin);
    }

    /**
     * Get langvar array from specified content
     * @param string $full_key - key for search like Languages::email_marketing.subscription_confirmed
     * @throws \InvalidArgumentException if $full_key is empty
     *
     * @return bool|array - [
     *  'msgctxt' => "Languages::payments.epdq.tbl_bgcolor",
     *  'msgid' =>  "Table background color",
     *  'msgstr' => "Table background color"
     * ]
     */
    public static function findByKeyIn(string $full_key, string $content)
    {
        if (!$full_key) {
            throw new \InvalidArgumentException('full_key cannot be empty');
        }

        $found_count = preg_match_all(
            '/(msgctxt\s+"(' . $full_key . ')")' . self::$eol . '+(msgid\s+"(.*)")' . self::$eol . '+(msgstr\s+"(.*)")/umi',
            $content,
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
     * Fully remove langvar, that matches msgctxt (msgctxt)
     * @param string $msgctxt - msgctxt
     * @throws \InvalidArgumentException if $msgctxt is empty
     *
     * @return LanguageGenerator
     */
    public function removeByKey(string $msgctxt)
    {
        if (!$msgctxt) {
            throw new \InvalidArgumentException('msgctxt cannot be empty');
        }

        $recycle_bin = '';
        $new_content = preg_replace_callback(
            '/(msgctxt\s+"' . $msgctxt . '"' . self::$eol . '+msgid\s+".*"' . self::$eol . '+msgstr\s+".*")(' . self::$eol . '*)/umi',
            function($matches) use (&$recycle_bin) {
                $recycle_bin .= $matches[1] . $matches[2];
                return '';
            },
            $this->content
        );

        $this->setContent($new_content);
        $this->appendRecycleBin($recycle_bin);

        return $this;
    }

    /**
     * Fully removes all langvars with a specified id
     * 
     * @param string $id
     * 
     * @return LanguageGenerator
     */
    public function removeById(string $id)
    {
        if (!$id) {
            throw new \InvalidArgumentException('id cannot be empty');
        }

        $recycle_bin = '';
        $new_content = preg_replace_callback(
            '/(msgctxt\s+"[\w:._]+' . $this->config->get('addon.id') . '::' . $id . '[\w:._]*"' . self::$eol . '+msgid\s+".*"' . self::$eol . '+msgstr\s+".*")(' . self::$eol . '*)/umi',
            function($matches) use (&$recycle_bin) {
                $recycle_bin .= $matches[1] . $matches[2];
                return '';
            },
            $this->content
        );

        $this->setContent($new_content);
        $this->appendRecycleBin($recycle_bin);

        return $this;
    }

    /**
     * Check for ending line and add it if not found
     * @param string $content - multiline content
     *
     * @return string - multiline content with trailing new line
     */
    public static function setEndingNewLine(string $content): string
    {
        $output_arr = explode(self::$eol, $content);

        if (!empty(end($output_arr))) {
            $output_arr[] = '';
        } elseif (end($output_arr) === '' && prev($output_arr) === '') {
            array_pop($output_arr);
        }

        return implode(self::$eol, $output_arr);
    }

    /**
     * The file must end with an empty line.
     * @inheritdoc
     */
    public function toString(): string
    {
        return self::purify($this->content);
    }

    /**
     * replace langvar if already exists with same msgctxt
     * and create new if not
     * @todo add langvar right after removed
     * @param string $msgctxt
     * @param string $msgid
     * @param string $msgstr - optional, gets value of $msgid if empty
     *
     * @return LanguageGenerator
     */
    public function replaceLangvar(string $msgctxt, string $msgid, string $msgstr = '')
    {
        if (empty($msgctxt)) {
            throw new \InvalidArgumentException('msgctxt cannot be empty');
        }

        $saved_langvar = $this->findByKeyInRecycleBin($msgctxt);

        if ($saved_langvar) {
            list('msgctxt' => $msgctxt, 'msgid' => $msgid, 'msgstr' => $msgstr) = $saved_langvar;
            $langvar_lines = [
                "msgctxt \"$msgctxt\"",
                "msgid \"$msgid\"",
                "msgstr \"$msgstr\""
            ];
        } else {
            $msgstr_actual = $msgstr ?: $msgid;

            $langvar_lines = [
                "msgctxt \"$msgctxt\"",
                "msgid \"$msgid\"",
                "msgstr \"$msgstr_actual\""
            ];

            $this->removeByKey($msgctxt);
        }
        
        $this->appendContent(implode(PHP_EOL, $langvar_lines));
       
        return $this;
    }

    /**
     * Checks langvars for edited manualy
     * If msgctxt "SettingsOptions::sd_addon::name" has msgid "Name"
     * So it didn't modified manually
     * because Name created from name id
     * but if it was msgid "Vendor name" - it was modified
     * 
     * @return bool
     */
    public static function checkForEdited(string $msgctxt, string $msgid): bool
    {
        $msg_parts = explode('::', $msgctxt);
        $last_item = end($msg_parts);

        return strcmp(parse_to_readable($last_item), $msgid) !== 0;
    }

    /**
     * add langvar
     * @param string $msgctxt
     * @param string $msgid
     * @param string $msgstr - optional, gets value of $msgid if empty
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
     * Clears multiple empty lines
     * 
     * @param string $content - content to be purified
     * 
     * @return string - purified content
     */
    public static function purify(string $content): string
    {
        return self::setEndingNewLine(
            self::clearWhitespaces($content)
        );
    }

    /**
     * Reduces multiple empty lines to one
     * 
     * @param string $content - content to be purified
     * 
     * @return string content without multiple whitespaces
     */
    public static function clearWhitespaces(string $content): string
    {
        return preg_replace('/(' . self::$eol . '{3,})/sm', str_repeat(self::$eol, 2), $content);
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
