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
        'create',
        'remove',
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
        $languageGenerator      = new LanguageGenerator($this->config);
        $generatorMediator      = new GeneratorMediator();

        $generatorMediator
            ->addGenerator($addonXmlGenerator)
            ->addGenerator($languageGenerator);

        $this->mfGenerator = new MultipleFileGenerator($this->filesystem);
        $this->mfGenerator
            ->addGenerator($addonXmlGenerator)
            ->addGenerator($languageGenerator);
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
     * addon-xml create
     * creates addonXml structure and write it to file
     * @throws Exception if file already exists
     */
    public function create()
    {
        $addonXmlFileGenerator = $this->mfGenerator
            ->find(AddonXmlGenerator::class);
        
        $addonXmlGenerator = $addonXmlFileGenerator
                ->throwIfExists('Such addon.xml already exists. Remove it first if you want to replace it.')
                ->extract();
                
        $addonXmlGenerator
            ->create();

        $addonXmlFileGenerator    
            ->write()
            ->throwIfNotExists($addonXmlGenerator->getPath() . ' cannot be created.');

        /**
         * results
         */
        $this->terminal->success($addonXmlGenerator->getPath() . ' was created');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare('', $addonXmlGenerator->toString()))
        );
    }

    /**
     * help:
     * addon-xml remove
     * removes file addon.xml
     * @throws Exception if file doesn't exists
     */
    public function remove()
    {
        $addonXmlGenerator = $this->mfGenerator
            ->find(AddonXmlGenerator::class)
                ->read()
                ->remove()
                ->throwIfExists('File cannot be removed.')
                ->extract();

        $this->terminal->success($addonXmlGenerator->getPath() . ' was removed');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare($addonXmlGenerator->toString(), ''))
        );
    }

    /**
     * help:
     * addon-xml/update --addon.id <addon_id> <item> [remove] [...args]
     * Sets additional field to addon xml file
     * addon.id - id of the addon
     * ---
     *      --settings-item - <item id="date">...</item>
     *           args: section=<section_id> type=<type> id=<id> [dv=<default_value>] [v=<variants>]
     *              section         - id for the settings section
     *              type            - type of the item id: input, textarea, password, checkbox, selectbox, multiple select, multiple checkboxes, countries list, states list, file, info, header, template
     *              id              - id of the setting item
     *              default_value   - (df) - default value for setting item
     *              variants        - (v) - list of item value variants comma separated
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
            $old_content[get_class($generator->extract())] = $generator->extract()->toString();
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
        
        $this->mfGenerator->each(function($generator) use ($old_content) {
            $this->terminal->diff(
                \Diff::toString(\Diff::compare($old_content[get_class($generator->extract())], $generator->extract()->toString()))
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
            $this->config->getOr('default_value', 'dv') ?: '',
            (function($config) {
                $variants = $config->getOr('variants', 'v');

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

    /**
     * Autocomplete addon param
     */
    public function createAutocomplete($prev = null, $cur = null, $arguments = [])
    {
        $generator = $this->mfGenerator
            ->find(AddonXmlGenerator::class)
                ->extract();

        return $this->autocomplete->combineQueueParam(
            $this->autocomplete->queueArgument('addon.id'),
            $this->autocomplete->queueArgument('addon.scheme', function() use ($generator) {
                return $generator->getVariants('scheme');
            }),
            $this->autocomplete->queueArgument('addon.status', function() use ($generator) {
                return $generator->getVariants('status');
            }),
            $this->autocomplete->queueArgument('addon.edition_type'),
            $this->autocomplete->queueArgument('addon.priority'),
            $this->autocomplete->queueArgument('addon.position')
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
        $autocomplete   = $this->autocomplete;
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
