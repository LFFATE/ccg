<?php

namespace controllers;

use generators\AddonXml\AddonXmlGenerator;
use generators\Language\LanguageGenerator;
use generators\Readme\ReadmeGenerator;
use generators\MultipleFileGenerator;
use terminal\Terminal;
use filesystem\Filesystem;
use mediators\GeneratorMediator;
use \Config;

class Addon extends AbstractController
{
    private $mfGenerator;
    private static $allowedMethods = [
        'help',
        'create',
        'remove'
    ];

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

        $addonXmlGenerator  = new AddonXmlGenerator($this->config);
        $languageGenerator  = new LanguageGenerator($this->config);
        $readmeGenerator    = new ReadmeGenerator($this->config);

        $generatorMediator  = new GeneratorMediator();

        $generatorMediator
            ->addGenerator($addonXmlGenerator)
            ->addGenerator($languageGenerator)
            ->addGenerator($readmeGenerator);

        $this->mfGenerator = new MultipleFileGenerator($this->filesystem);
        $this->mfGenerator
            ->addGenerator($addonXmlGenerator)
            ->addGenerator($languageGenerator)
            ->addGenerator($readmeGenerator);
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
     * addon create
     * creates addon.xml, language file, readme and other
     * @throws Exception if file already exists
     */
    public function create()
    {
        $this->mfGenerator
            ->throwIfExists('File already exists. Remove it first if you want to replace it.')
            ->find(AddonXmlGenerator::class)
                ->extract()
                    ->create();

        $this->mfGenerator
            ->excluding(ReadmeGenerator::class)
                ->write()
                ->throwIfNotExists('File cannot be created.');

        $this->mfGenerator
            ->find(ReadmeGenerator::class)
                ->readFromTemplate()
                ->write();

        $self = $this;
        $this->mfGenerator->each(function($generator) use ($self) {
            $self->terminal->success($generator->extract()->getPath() . ' was created');
            $self->terminal->diff(
                \Diff::toString(\Diff::compare('', $generator->extract()->toString()))
            );
        });
    }
    
    /**
     * help:
     * addon remove
     * removes entire path of the addon
     */
    public function remove()
    {
        $self = $this;
        $this->terminal->confirm(
            function() use ($self) {
                $addonPath = $self->config->get('path')
                    . $self->config->get('filesystem.output_path_relative');

                $self->filesystem->delete($addonPath);

                if ($self->filesystem->exists($addonPath)) {
                    throw new \Exception($addonPath . ' cannot be removed');
                }

                $this->terminal->warning('Addon was removed');
            }
        );        
    }

    /**
     * Autocomplete addon param
     */
    public function createAutocomplete($prev = null, $cur = null, $arguments = [])
    {
        if (empty($arguments['addon.id'])) {
            return ['--addon.id'];
        }

        return [];
    }
    /**
     * Autocomplete addon name to be removed
     */
    public function removeAutocomplete($prev = null, $cur = null, $arguments = [])
    {
        switch ($prev)
        {
            case '--addon.id':
           
                $addonsPath = sanitize_filename(
                    $this->config->get('path')
                    . $this->config->get('filesystem.output_path_relative') . '../'
                );

                $dirs = $this->filesystem->listDirs($addonsPath);
        
                return array_map(function($dir) {
                    $paths = explode('/', $dir);
                    return end($paths);
                }, $dirs);
            break;
            default:
            /**
             * @todo do it better
             */
                if (empty($arguments['addon.id'])) {
                    return ['--addon.id'];
                }

                return [];
        }
    }
}
