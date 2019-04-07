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
    private $addonXmlGenerator;
    private $languageGenerator;
    private $readmeGenerator;

    public function addGenerator(AbstractGenerator $generator)
    {
        switch(get_class($generator))
        {
            case 'generators\AddonXml\AddonXmlGenerator':
                $this->addonXmlGenerator = $generator;
                break;
            case 'generators\Language\LanguageGenerator':
                $this->languageGenerator = $generator;
                break;
            case 'generators\Readme\ReadmeGenerator':
                $this->readmeGenerator = $generator;
                break;
            default:
                throw new \LogicException('Wrong generator type passed: ' . get_class($generator));
        }

        $generator->setMediator($this);
    }

    public function getAddonXmlGenerator(): AddonXmlGenerator
    {
        return $this->addonXmlGenerator;
    }

    public function getLanguageGenerator(): LanguageGenerator
    {
        return $this->languageGenerator;
    }

    public function getReadmeGenerator(): ReadmeGenerator
    {
        return $this->readmeGenerator;
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
                $this->languageGenerator
                    ->create()
                    ->addLangvar(
                        LanguageGenerator::getTranslationKey(
                            LangvarTypes::$ADDONS,
                                'name',
                                $data['addon.id'],
                            ),
                        parse_to_readable($data['addon.id'])
                    )
                    ->addLangvar(
                        LanguageGenerator::getTranslationKey(
                            LangvarTypes::$ADDONS,
                                'description',
                                $data['addon.id'],
                            ),
                        parse_to_readable($data['addon.id'])
                    );
            break;
            case 'addonxml.setting.updated':
            case 'addonxml.setting.added':
                $this->languageGenerator
                    ->replaceLangvar(
                        LanguageGenerator::getTranslationKey(
                            LangvarTypes::$SETTINGS_OPTIONS,
                                $data['addon.id'],
                                $data['id'],
                            ),
                        parse_to_readable($data['id'])
                    )
                    ->replaceLangvar(
                        LanguageGenerator::getTranslationKey(
                            LangvarTypes::$SETTINGS_TOOLTIPS,
                                $data['addon.id'],
                                $data['id'],
                            ),
                        parse_to_readable($data['id'])
                    );

                if (!empty($data['variants'])) {
                    foreach($data['variants'] as $variation) {
                        $this->languageGenerator
                            ->replaceLangvar(
                                LanguageGenerator::getTranslationKey(
                                    LangvarTypes::$SETTINGS_VARIANTS,
                                        $data['addon.id'],
                                        $data['id'],
                                        $variation,
                                    ),
                                parse_to_readable($data['id'])
                            );
                    }
                }

            break;
            case 'addonxml.settingSection.added':
            case 'addonxml.settingSection.updated':
                $this->languageGenerator
                    ->replaceLangvar(
                        LanguageGenerator::getTranslationKey(
                            LangvarTypes::$SETTINGS_SECTIONS,
                                $data['addon.id'],
                                $data['id'],
                            ),
                        parse_to_readable($data['id'])
                    );
            break;
        }
    }
}
