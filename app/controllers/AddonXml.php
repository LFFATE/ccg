<?php

namespace controllers;

use generators\AddonXml\AddonXmlGenerator;
use generators\Language\LanguageGenerator;
use generators\MultipleFileGenerator;
use terminal\Terminal;
use filesystem\Filesystem;
use autocomplete\Autocomplete;
use mediators\GeneratorMediator;
use \Config;

class AddonXml extends AbstractController
{
    private $mfGenerator;
    private static $allowedMethods = [
        'help',
        'update'
    ];

    use HelpTrait;

    function __construct(
        Config              $config,
        Terminal            $terminal,
        Filesystem          $filesystem,
        Autocomplete        $autocomplete
    )
    {
        $this->config               = $config;
        $this->terminal             = $terminal;
        $this->filesystem           = $filesystem;
        $this->autocomplete         = $autocomplete;

        $addonXmlGenerator      = new AddonXmlGenerator($this->config);
        $generatorMediator      = new GeneratorMediator();

        $generatorMediator->addGenerator($addonXmlGenerator);

        $this->mfGenerator = new MultipleFileGenerator($this->filesystem);
        $this->mfGenerator->addGenerator($addonXmlGenerator);
        
        // handle all supported languages
        $supported_languages = $this->config->get('addon.supported_languages');
        if ($supported_languages) {
            $supported_languages_list = explode(',', $supported_languages);
        }

        $self = $this;
        array_walk($supported_languages_list, function($language) use ($self, $generatorMediator) {
            $languageGenerator = new LanguageGenerator($self->config, $language);
            $generatorMediator->addGenerator($languageGenerator);
            $self->mfGenerator->addGenerator($languageGenerator);
        });
    }

    /**
     * @inheritdoc
     */
    public static function getAllowedMethods(): array
    {
        return self::$allowedMethods;
    }

    /**
     * help:
     * addon-xml/update --addon.id <addon_id> --set <item> [...args]
     * Sets additional field to addon xml file
     * addon.id - id of the addon
     * ---
     *      --set
     *          settings-item - <item id="date">...</item>
     *              args: --section <section_id> --type <type> --id <id> [--default_value <default_value>] [--variants "<variant1,variant2>"]
     *                  section         - id for the settings section
     *                  type            - type of the item id: input, textarea, password, checkbox, selectbox, multiple select, multiple checkboxes, countries list, states list, file, info, header, template
     *                  id              - id of the setting item
     *                  default_value   - default value for setting item
     *                  variants        - list of item value variants comma separated and quote wrapped
     * ---
     * 
     * see more @link [https://www.cs-cart.ru/docs/4.9.x/developer_guide/addons/scheme/scheme3.0_structure.html]
     * @throws Exception if file doesn't exists
     */
    public function update()
    {
        $this->mfGenerator
            ->throwIfNotExists('Some addon file not found.')
            ->read();

        $old_content = [];
        $this->mfGenerator->each(function($generator) use (&$old_content) {
            $old_content[$generator->extract()->getKey()] = $generator->extract()->toString();
        });
        
        $addonXmlGenerator = $this->mfGenerator
            ->find(AddonXmlGenerator::class)
                ->extract();

        $method     = $this->getMethodName();
        $class_name = get_class($this);
        if (method_exists($class_name, $method)) {
            $this->{$method}($addonXmlGenerator);
        } else {
            throw new \BadMethodCallException('There is no such command');
        }

        $this->mfGenerator
            ->write()
            ->throwIfNotExists();
        
        $self = $this;
        $this->mfGenerator->each(function($generator) use ($self, $old_content) {
            $self->terminal->success($generator->extract()->getPath() . ' was changed');
            $self->terminal->diff(
                \Diff::toString(\Diff::compare($old_content[$generator->extract()->getKey()], $generator->extract()->toString()))
            );
        });
    }

    /**
     * 
     */
    public function setSettingsItem($addonXmlGenerator)
    {
        $addonXmlGenerator->setSetting(
            $this->config->get('section'),
            $this->config->get('type'),
            $this->config->get('id'),
            $this->config->get('default_value') ?: '',
            (function($config) {
                $variants = $config->get('variants');

                return $variants ? explode(',', $variants) : [];
            })($this->config)
        );
    }

    public function removeSettingsItem($addonXmlGenerator)
    {
        $addonXmlGenerator->removeSetting(
            $this->config->get('id')
        );
    }

    public function updateAutocomplete()
    {
        $autocomplete   = $this->autocomplete;
        $arguments      = $this->terminal->getArguments();
        
        if (!empty($arguments['set']) && $arguments['set'] === 'settings-item') {
            return $this->setSettingsItemAutocomplete();
        }

        return $this->autocomplete->combineQueueParam(
            $this->autocomplete->queueArgument('addon.id', function() use ($autocomplete) {
                return $autocomplete->getAddonsList();
            }),
            $this->autocomplete->queueArgument('set', ['settings-item'])
        );
    }

    public function setSettingsItemAutocomplete()
    {
        $generator      = $this->mfGenerator
            ->find(AddonXmlGenerator::class)
                ->extract();

        return $this->autocomplete->combineQueueParam(
            $this->autocomplete->queueArgument('type', function() use ($generator) {
                return $generator->getVariants('item');
            }),
            $this->autocomplete->queueArgument('section'),
            $this->autocomplete->queueArgument('id')
        );
    }
}
