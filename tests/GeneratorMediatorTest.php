<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;
use mediators\GeneratorMediator;
use mediators\AbstractMediator;
use generators\Language\LanguageGenerator;
use generators\AddonXml\AddonXmlGenerator;
use generators\AbstractGenerator;


final class GeneratorMediatorTest extends TestCase
{
    private $config;

    public function testCanBeCreated(): void
    {
        $this->config = new Config([
            'addon.id=sd_addon'
        ],
        [
            'addon.default_language' => 'en'
        ]);

        $addonXmlGenerator = new AddonXmlGenerator($this->config);
        $languageGenerator = new LanguageGenerator($this->config);
        $generatorMediator = new GeneratorMediator();
        $generatorMediator->addGenerator($addonXmlGenerator);
        $generatorMediator->addGenerator($languageGenerator);

        $this->expectException(\LogicException::class);
        $generatorMediator->addGenerator(new TestGenerator());
    }
}

class TestGenerator extends AbstractGenerator
{
    public function setContent(string $content)
    {
    }

    public function toString(): string
    {
        return '';
    }

    public function setMediator(AbstractMediator $mediator): void
    {
    }

    public function getPath(): string
    {
        return '';
    }

    public function getTemplateFilename(): string
    {
        return '';
    }
}
