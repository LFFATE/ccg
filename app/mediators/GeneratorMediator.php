<?php

namespace mediators;

use generators\AbstractGenerator;
use generators\AddonXml\AddonXmlGenerator;
use generators\Language\LanguageGenerator;
use generators\Readme\ReadmeGenerator;
use generators\Language\enums\LangvarTypes;
use mediators\AbstractMediator;

class GeneratorMediator extends AbstractMediator
{
    private $addonXmlGenerators;
    private $languageGenerators;
    private $readmeGenerators;

    public function addGenerator(AbstractGenerator $generator)
    {
        switch(get_class($generator))
        {
            case 'generators\AddonXml\AddonXmlGenerator':
                $this->addonXmlGenerators[] = $generator;
                break;
            case 'generators\Language\LanguageGenerator':
                $this->languageGenerators[] = $generator;
                break;
            case 'generators\Readme\ReadmeGenerator':
                $this->readmeGenerators[] = $generator;
                break;
            default:
                throw new \LogicException('Wrong generator type passed: ' . get_class($generator));
        }

        $generator->setMediator($this);

        return $this;
    }

    /**
     * Handle side effects for any generation
     * For example: create langvars for new settings added by addonXmlGenerator
     * @param string $name - name or code of the event: 'addonxml.setting.added'
     * @param mixed $data - any data from event
     * @param $sender - component which triggered the event
     *
     */
    public function trigger(string $name, $data = [], $sender = null): void
    {
        switch ($name)
        {
            case 'addonxml.created':
                array_walk($this->languageGenerators, function($generator) use ($data) {
                    $generator
                        ->create()
                        ->addLangvar(
                            LanguageGenerator::getTranslationKey(
                                LangvarTypes::$ADDONS,
                                    'name',
                                    $data['addon.id']
                                ),
                            parse_to_readable($data['addon.id'])
                        )
                        ->addLangvar(
                            LanguageGenerator::getTranslationKey(
                                LangvarTypes::$ADDONS,
                                    'description',
                                    $data['addon.id']
                                ),
                            parse_to_readable($data['addon.id'])
                        );
                });
            break;
            case 'addonxml.setting.updated':
            case 'addonxml.setting.added':
                
                array_walk($this->languageGenerators, function($generator) use ($data) {
                    $generator
                        ->removeById($data['id'])
                        ->replaceLangvar(
                            LanguageGenerator::getTranslationKey(
                                LangvarTypes::$SETTINGS_OPTIONS,
                                    $data['addon.id'],
                                    $data['id']
                                ),
                            parse_to_readable($data['id'])
                        )
                        ->replaceLangvar(
                            LanguageGenerator::getTranslationKey(
                                LangvarTypes::$SETTINGS_TOOLTIPS,
                                    $data['addon.id'],
                                    $data['id']
                                ),
                            parse_to_readable($data['id'])
                        );
                });
                if (!empty($data['variants'])) {
                    foreach($data['variants'] as $variation) {
                        array_walk($this->languageGenerators, function($generator) use ($data, $variation) {
                            $generator
                                ->replaceLangvar(
                                    LanguageGenerator::getTranslationKey(
                                        LangvarTypes::$SETTINGS_VARIANTS,
                                            $data['addon.id'],
                                            $data['id'],
                                            $variation
                                        ),
                                    parse_to_readable($variation)
                                );
                        });
                    }
                }

            break;
            case 'addonxml.setting.removed':
                array_walk($this->languageGenerators, function($generator) use ($data) {
                    $generator
                        ->removeById($data['id']);
                });
            break;
            case 'addonxml.settingSection.added':
            case 'addonxml.settingSection.updated':
                array_walk($this->languageGenerators, function($generator) use ($data) {
                    $generator
                        ->replaceLangvar(
                            LanguageGenerator::getTranslationKey(
                                LangvarTypes::$SETTINGS_SECTIONS,
                                    $data['addon.id'],
                                    $data['id']
                                ),
                            parse_to_readable($data['id'])
                        );
                    });
            break;
        }
    }
}
