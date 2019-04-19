<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

use filesystem\Filesystem;

final class FilesystemTest extends TestCase
{
    private $testFilename = __DIR__ . '/tmp/test';
    private $testPath = __DIR__ . '/tmp/dir';
    private $fileContent = <<<EOD
    Test string
    towrite to file
    lorem ipsum dikit ist amet
EOD;

    public function testCanBeCreated(): void
    {
        $filesystem = new Filesystem();

        $this->assertInstanceOf(
            Filesystem::class,
            $filesystem
        );
    }

    public function testCanWriteFile(): void
    {
        $filesystem = new Filesystem();

        $filesystem->write($this->testFilename, $this->fileContent);
        $this->assertFileExists(
            $this->testFilename
        );
        $this->assertStringEqualsFile(
            $this->testFilename,
            $filesystem->read($this->testFilename)
        );
    }

    public function testExists(): void
    {
        $filesystem = new Filesystem();

        $this->assertTrue(
            $filesystem->exists($this->testFilename)
        );
    }

    public function testCanRemove(): void
    {
        $filesystem = new Filesystem();

        $is_deleted = $filesystem->delete($this->testFilename);

        $this->assertFileNotExists(
            $this->testFilename
        );

        $this->assertTrue($is_deleted);
    }

    public function testCanRemoveDir(): void
    {
        $filesystem = new Filesystem();
        $filesystem->write($this->testPath . '/subdir/susubdir/tst.txt', '');
        $filesystem->write($this->testPath . '/subdir/txt.txt', '');
        $filesystem->write($this->testPath . '/file.php', '');
        
        $filesystem->delete($this->testPath);

        $this->assertFileNotExists(
            $this->testPath
        );
    }

    public function testRename(): void
    {
        $filesystem = new Filesystem();
        $test_file = $this->testPath . '/to_rename.js';
        $test_file_not_exists = $this->testPath . '/not_exists.js';
        $test_file_renamed = $this->testPath . '/renamed.js';
        $filesystem->write($test_file, 'rename file');

        $this->assertFileExists(
            $test_file
        );

        $filesystem->rename($test_file, 'renamed.js');
        
        $this->assertFileNotExists(
            $test_file
        );
        
        $this->assertFileExists(
            $test_file_renamed
        );
        
        $this->expectException(\InvalidArgumentException::class);
        $filesystem->rename($test_file, 'renamed.js');
        $this->expectException(\InvalidArgumentException::class);
        $filesystem->rename($test_file_not_exists, 'renamed.js');
        $filesystem->delete($test_file_renamed);
    }
}
