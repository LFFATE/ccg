<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use generators\Readme\ReadmeGenerator;

final class ReadmeGeneratorTest extends TestCase
{
    private $config;
    private $testFilename = ROOT_DIR . '/tests/sources/md/README.md';
    private $testFilenameResult = ROOT_DIR . '/tests/sources/md/README_result.md';

    protected function setUp(): void
    {
        $this->config = new Config([
            'addon.id=sd_addon'
        ],
        [
            'filesystem.output_path_relative' => './',
            'developer.name' => 'Mikhail',
            'developer.company' => 'Simtechdev'
        ]);
    }

    public function testCanCreate(): void
    {
        $generator = new ReadmeGenerator($this->config);

        $this->assertInstanceOf(
            ReadmeGenerator::class,
            $generator
        );
    }

    /**
     * @covers generators\Readme\ReadmeGenerator::getPath
     */
    public function testGetPath(): void
    {
        $generator = new ReadmeGenerator($this->config);

        $this->assertSame(
            get_absolute_path($this->config->get('path') . $this->config->get('filesystem.output_path_relative') . '/app/addons/sd_addon/README.md'),
            $generator->getPath()
        );
    }

    /**
     * @covers generators\Readme\ReadmeGenerator::setContent
     * @covers generators\Readme\ReadmeGenerator::toString
     * 
     */
    public function testGenerate(): void
    {
        $generator = new ReadmeGenerator($this->config);

        $generator->setContent(
            file_get_contents($this->testFilename)
        );

        $this->assertStringEqualsFile($this->testFilenameResult, $generator->toString());
    }
}
