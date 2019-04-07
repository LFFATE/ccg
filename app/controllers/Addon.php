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

class Addon extends AbstractController
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
     * addon create
     * creates addon.xml, language file, readme and other
     * @throws Exception if file already exists
     */
    public function create()
    {
        $addonXmlGenerator  = new AddonXmlGenerator($this->config);
        $languageGenerator  = new LanguageGenerator($this->config);
        $readmeGenerator    = new ReadmeGenerator($this->config);
        $generatorMediator  = new GeneratorMediator();
        $generatorMediator->addGenerator($addonXmlGenerator);
        $generatorMediator->addGenerator($languageGenerator);
        $generatorMediator->addGenerator($readmeGenerator);

        $addonXmlFileGenerator  = new FileGenerator($addonXmlGenerator, $this->filesystem);
        $languageFileGenerator  = new FileGenerator($languageGenerator, $this->filesystem);
        $readmeFileGenerator    = new FileGenerator($readmeGenerator, $this->filesystem);

        $addonXmlFileGenerator->throwIfExists($addonXmlGenerator->getPath() . ' already exists. Remove it first if you want to replace it.');
        $languageFileGenerator->throwIfExists($languageGenerator->getPath() . ' already exists. Remove it first if you want to replace it.');

        $addonXmlGenerator->create();

        $addonXmlFileGenerator
            ->write()
            ->throwIfNotExists($addonXmlGenerator->getPath() . ' cannot be created.');
        $languageFileGenerator
            ->write()
            ->throwIfNotExists($languageGenerator->getPath() . ' cannot be created.');
        $readmeFileGenerator
            ->readFromTemplate()
            ->write();

        /**
         * results
         */
        $this->terminal->success($addonXmlGenerator->getPath() . ' was created');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare('', $addonXmlGenerator->toString()))
        );

        $this->terminal->success($languageGenerator->getPath() . ' was created');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare('', $languageGenerator->toString()))
        );

        $this->terminal->success($readmeGenerator->getPath() . ' was created');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare('', $readmeGenerator->toString()))
        );
    }

    /**
     * help:
     * addon remove
     * removes entire path of the addon
     */
    public function remove()
    {
        $addonPath = $this->config->get('path')
            . $this->config->get('filesystem.output_path_relative');

        $this->filesystem->delete($addonPath);

        if ($this->filesystem->exists($addonPath)) {
            throw new \Exception($addonPath . ' cannot be removed');
        }
    }
}
