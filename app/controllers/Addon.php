<?php

namespace controllers;

use generators\AddonXml\AddonXmlGenerator;
use generators\Language\LanguageGenerator;
use generators\Readme\ReadmeGenerator;
use generators\MultipleFileGenerator;
use terminal\Terminal;
use filesystem\Filesystem;
use autocomplete\Autocomplete;
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
        Filesystem          $filesystem,
        Autocomplete        $autocomplete
    )
    {
        $this->config               = $config;
        $this->terminal             = $terminal;
        $this->filesystem           = $filesystem;
        $this->autocomplete         = $autocomplete;

        $addonXmlGenerator      = new AddonXmlGenerator($this->config);
        $readmeGenerator        = new ReadmeGenerator($this->config);

        $generatorMediator  = new GeneratorMediator();

        $generatorMediator
            ->addGenerator($addonXmlGenerator)
            ->addGenerator($readmeGenerator);

        $this->mfGenerator = new MultipleFileGenerator($this->filesystem);
        $this->mfGenerator
            ->addGenerator($addonXmlGenerator)
            ->addGenerator($readmeGenerator);
        
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
     * addon/create
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
                $addonPath = $self->config->get('filesystem.output_path');

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

    /**
     * Autocomplete addon name to be removed
     */
    public function removeAutocomplete($prev = null, $cur = null, $arguments = [])
    {
        $self = $this;
        
        return $this->autocomplete->combineQueueParam(
            $this->autocomplete->queueArgument('addon.id', function() use ($self) {
                return $self->autocomplete->getAddonsList();
            })
        );
    }
}
