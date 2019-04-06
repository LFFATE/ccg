<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

use filesystem\Filesystem;

final class FilesystemTest extends TestCase
{
    private $testFilename = __DIR__ . '/tmp/' . 'test';
    private $fileContent = <<<EOD
    Test string
    towrite to file
    lorem ipsum dikit ist amet
EOD;

    public function testCanBeCreated(): void
    {
        $Filesystem = new Filesystem();

        $this->assertInstanceOf(
            Filesystem::class,
            $Filesystem
        );
    }

    public function testCanWriteFile(): void
    {
        $Filesystem = new Filesystem();

        $Filesystem->write($this->testFilename, $this->fileContent);
        $this->assertFileExists(
            $this->testFilename
        );
        $this->assertStringEqualsFile(
            $this->testFilename,
            $Filesystem->read($this->testFilename)
        );
    }

    public function testExists(): void
    {
        $Filesystem = new Filesystem();

        $this->assertTrue(
            $Filesystem->exists($this->testFilename)
        );
    }

    public function testCanRemoveFile(): void
    {
        $Filesystem = new Filesystem();

        $is_deleted = $Filesystem->delete($this->testFilename);

        $this->assertFileNotExists(
            $this->testFilename
        );

        $this->assertTrue($is_deleted);
    }
}
