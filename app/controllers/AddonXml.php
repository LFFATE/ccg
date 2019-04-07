<?php

namespace controllers;

use generators\AddonXml\AddonXmlGenerator;
use generators\Language\LanguageGenerator;
use generators\Readme\ReadmeGenerator;
use generators\FileGenerator;
use terminal\Terminal;
use filesystem\Filesystem;
use mediators\GeneratorMediator;
use \Config;

class AddonXml extends AbstractController
{
    private $config;
    private $terminal;
    private $filesystem;

    use HelpTrait;

    function __construct(
        Config              $config,
        Terminal            $terminal,
        Filesystem          $filesystem
    )
    {
        $this->config               = $config;
        $this->terminal             = $terminal;
        $this->filesystem           = $filesystem;
    }

    /**
     * help:
     * addonXml create
     * creates addonXml structure and write it to file
     * @throws Exception if file already exists
     */
    private function create()
    {
        $addonXmlGenerator  = new AddonXmlGenerator($this->config);
        $generatorMediator  = new GeneratorMediator();
        $generatorMediator->addGenerator($addonXmlGenerator);

        $addonXmlFileGenerator  = new FileGenerator($addonXmlGenerator, $this->filesystem);

        $addonXmlFileGenerator->throwIfExists($addonXmlGenerator->getPath() . ' already exists. Remove it first if you want to replace it.');

        $addonXmlGenerator->create();

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
     * addonXml remove
     * removes file addon.xml
     * @throws Exception if file doesn't exists
     */
    private function remove()
    {
        $addonXmlGenerator      = new AddonXmlGenerator($this->config);
        $languageGenerator      = new LanguageGenerator($this->config);
        $generatorMediator      = new GeneratorMediator();
        $generatorMediator->addGenerator($addonXmlGenerator);
        $generatorMediator->addGenerator($languageGenerator);
        $addonXmlFileGenerator  = new FileGenerator($addonXmlGenerator, $this->filesystem);
        $languageFileGenerator  = new FileGenerator($languageGenerator, $this->filesystem);

        $addonXmlFileGenerator
            ->read()
            ->remove()
            ->throwIfExists($addonXmlGenerator->getPath() . ' cannot be removed.');

        $languageFileGenerator
            ->read()
            ->remove()
            ->throwIfExists($languageGenerator->getPath() . ' cannot be removed.');

        $this->terminal->success($addonXmlGenerator->getPath() . ' was removed');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare($addonXmlGenerator->toString(), ''))
        );

        $this->terminal->success($languageGenerator->getPath() . ' was removed');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare($languageGenerator->toString(), ''))
        );
    }

    /**
     * help:
     * addonXml update addon=<addon_id> set=<item> [...args]
     * Sets additional field to addon xml file
     * addon_id - id of the addon
     * set      - item to update at addon xml
     * ---
     *      settings item - si - <item id="date">...</item>
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
    private function update()
    {
        $addonXmlGenerator      = new AddonXmlGenerator($this->config);
        $languageGenerator      = new LanguageGenerator($this->config);
        $generatorMediator      = new GeneratorMediator();
        $generatorMediator->addGenerator($addonXmlGenerator);
        $generatorMediator->addGenerator($languageGenerator);
        $addonXmlFileGenerator  = new FileGenerator($addonXmlGenerator, $this->filesystem);
        $languageFileGenerator  = new FileGenerator($languageGenerator, $this->filesystem);

        $addonXmlFileGenerator
            ->throwIfNotExists($addonXmlGenerator->getPath() . ' not found.')
            ->read();

        $languageFileGenerator
            ->throwIfNotExists($languageGenerator->getPath() . ' not found.')
            ->read();

        $addon_xml_old_content  = $addonXmlGenerator->toString();
        $language_old_content   = $languageGenerator->toString();
        $set_item = $this->config->get('set');

        switch ($set_item)
        {
            case 'settings item':
            case 'si':
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
            break;
            default:
                throw new \BadMethodCallException('There is no such command: ' . $set_item);
        }

        $addonXmlFileGenerator
            ->write()
            ->throwIfNotExists();
        $languageFileGenerator
            ->write()
            ->throwIfNotExists();

        $this->terminal->diff(
            \Diff::toString(\Diff::compare($addon_xml_old_content, $addonXmlGenerator->toString()))
        );
        $this->terminal->diff(
            \Diff::toString(\Diff::compare($language_old_content, $languageGenerator->toString()))
        );
    }
}
