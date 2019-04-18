<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use generators\MultipleFileGenerator;
use generators\FileGenerator;
use generators\Readme\ReadmeGenerator;
use generators\AddonXml\AddonXmlGenerator;
use generators\Language\LanguageGenerator;
use filesystem\Filesystem;

final class MultipleFileGeneratorTest extends TestCase
{
    private $config;
    private $mfGenerator;

    protected function setUp(): void
    {
        $this->config = new Config([
            'addon.id=sd_new_addon'
        ],
        [
            'lang' => 'en',
            'filesystem.output_path_relative' => '/tests/sources/cscart/${addon.id}/'
        ]);

        $this->filesystem = new Filesystem();
        $this->mfGenerator = new MultipleFileGenerator($this->filesystem);
    }
    
    public function testCanCreate(): void
    {
        $this->assertInstanceOf(
            MultipleFileGenerator::class,
            $this->mfGenerator
        );
    }

    /**
     * @covers generators\MultipleFileGenerator::addGenerator
     * @covers generators\MultipleFileGenerator::find
     */
    public function testAddAndFindGenerator(): void
    {
        $readmeGenerator = new ReadmeGenerator($this->config);
        $foundGenerator = $this->mfGenerator
            ->addGenerator($readmeGenerator)
            ->find(ReadmeGenerator::class);

        $this->assertInstanceOf(
            FileGenerator::class,
            $foundGenerator
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->mfGenerator->find(LanguageGenerator::class);
    }

    /**
     * @covers generators\MultipleFileGenerator::read
     */
    public function testRead(): void
    {
        $languageGenerator = new LanguageGenerator($this->config);
        $addonXmlGenerator = new AddonXmlGenerator($this->config);
        $this->mfGenerator
            ->addGenerator($languageGenerator)
            ->addGenerator($addonXmlGenerator)
            ->read();

        $this->assertStringEqualsFile(
            $languageGenerator->getPath(),
            $languageGenerator->toString()
        );
        $this->assertStringEqualsFile(
            $addonXmlGenerator->getPath(),
            $addonXmlGenerator->toString()
        );
    }

    /**
     * @covers generators\MultipleFileGenerator::exists
     */
    public function testExists(): void
    {
        $languageGenerator = new LanguageGenerator($this->config);
        $addonXmlGenerator = new AddonXmlGenerator($this->config);
        
        $this->mfGenerator
            ->addGenerator($languageGenerator)
            ->addGenerator($addonXmlGenerator);
        
        $this->assertSame(
            true,
            $this->mfGenerator->exists()
        );

        $readmeGenerator = new ReadmeGenerator($this->config);
        $this->mfGenerator
            ->addGenerator($readmeGenerator)
            ->find(ReadmeGenerator::class)
            ->remove();

        $this->assertSame(
            false,
            $this->mfGenerator->exists()
        );
    }

    /**
     * @covers generators\MultipleFileGenerator::readFromTemplate
     * @covers generators\MultipleFileGenerator::write
     * @covers generators\MultipleFileGenerator::remove
     * @covers generators\MultipleFileGenerator::throwIfExists
     * @covers generators\MultipleFileGenerator::throwIfNotExists
     */
    public function testRemove(): void
    {
        $readmeGenerator = new ReadmeGenerator($this->config);
        $this->mfGenerator->addGenerator($readmeGenerator);

        $this->mfGenerator
            ->readFromTemplate()
            ->write();

        $this->assertFileExists(
            $readmeGenerator->getPath()
        );

        $this->expectException(\UnexpectedValueException::class);
        $this->mfGenerator->throwIfExists('');

        $this->mfGenerator->remove();
        
        $this->assertFileNotExists(
            $readmeGenerator->getPath()
        );
        $this->expectException(\UnexpectedValueException::class);
        $this->mfGenerator->throwIfNotExists('');
    }

    /**
     * @covers generators\MultipleFileGenerator::removeGenerator
     */
    public function testRemoveGenerator(): void
    {
        $languageGenerator = new LanguageGenerator($this->config);
        $addonXmlGenerator = new AddonXmlGenerator($this->config);
        
        $this->mfGenerator
            ->addGenerator($languageGenerator)
            ->addGenerator($addonXmlGenerator);

        $this->assertInstanceOf(
            FileGenerator::class,
            $this->mfGenerator->find(AddonXmlGenerator::class)
        );

        $this->mfGenerator->removeGenerator(AddonXmlGenerator::class);
        $this->assertInstanceOf(
            FileGenerator::class,
            $this->mfGenerator->find(LanguageGenerator::class)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->assertInstanceOf(
            FileGenerator::class,
            $this->mfGenerator->find(AddonXmlGenerator::class)
        );
    }

    /**
     * @covers generators\MultipleFileGenerator::including
     */
    public function testIncluding(): void
    {
        $languageGenerator = new LanguageGenerator($this->config);
        $addonXmlGenerator = new AddonXmlGenerator($this->config);
        
        $this->mfGenerator
            ->addGenerator($languageGenerator);

        $this->assertInstanceOf(
            FileGenerator::class,
            $this->mfGenerator
                ->including($addonXmlGenerator)
                ->find(AddonXmlGenerator::class)
        );
    }

    /**
     * @covers generators\MultipleFileGenerator::excluding
     */
    public function testExcluding(): void
    { 
        $languageGenerator = new LanguageGenerator($this->config);
        $addonXmlGenerator = new AddonXmlGenerator($this->config);
        
        $this->mfGenerator
            ->addGenerator($languageGenerator)
            ->addGenerator($addonXmlGenerator);

        $this->assertInstanceOf(
            FileGenerator::class,
            $this->mfGenerator->find(LanguageGenerator::class)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->mfGenerator
                ->excluding(LanguageGenerator::class)
                ->find(LanguageGenerator::class);
    }

    public function testEach(): void
    {
        $languageGenerator = new LanguageGenerator($this->config);
        $addonXmlGenerator = new AddonXmlGenerator($this->config);
        
        $this->mfGenerator
            ->addGenerator($languageGenerator)
            ->addGenerator($addonXmlGenerator);
        
        $i = 0;

        $this->mfGenerator->each(function() use (&$i) {
            $i++;
        });

        $this->assertSame(2, $i);
    }
}

