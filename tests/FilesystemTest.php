<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

use filesystem\Filesystem;

final class FilesystemTest extends TestCase
{
    private $testFilename = __DIR__ . '/tmp/filesystem/test';
    protected static $testPath = __DIR__ . '/tmp/filesystem';
    protected $filesystem;
    private $fileContent = <<<EOD
    Test string
    towrite to file
    lorem ipsum dikit ist amet
EOD;

    public function setUp(): void
    {
        mkdir(self::$testPath, 0777, true);
        $this->filesystem = new Filesystem();
        $test_file = self::$testPath . '/to_rename.js';
        $this->filesystem->write($test_file, 'rename file');
    }

    /**
     * @covers filesystem\Filesystem::delete
     */
    public function tearDown(): void
    {
        $this->filesystem->delete(self::$testPath);
    }

    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(Filesystem::class, $this->filesystem);
    }

    public function testCanWriteAndDeleteFile(): void
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

        $this->assertTrue(
            $filesystem->exists($this->testFilename)
        );

        $is_deleted = $filesystem->delete($this->testFilename);

        $this->assertFileNotExists(
            $this->testFilename
        );

        $this->assertTrue($is_deleted);
    }

    public function testCanRemoveDir(): void
    {
        $filesystem = new Filesystem();
        $filesystem->write(self::$testPath . '/sub/subdir/susubdir/tst.txt', '');
        $filesystem->write(self::$testPath . '/sub/subdir/txt.txt', '');
        $filesystem->write(self::$testPath . '/sub/file.php', '');
        
        $filesystem->delete(self::$testPath . '/sub');

        $this->assertFileNotExists(
            self::$testPath . '/sub'
        );
    }

    public function testRename(): void
    {
        $filesystem = new Filesystem();
        $test_file = self::$testPath . '/to_rename.js';
        $test_file_not_exists = self::$testPath . '/not_exists.js';
        $test_file_renamed = self::$testPath . '/renamed.js';
        $filesystem->write($test_file, 'rename file');
        
        $this->assertFileExists($test_file);
        
        $filesystem->rename($test_file, 'renamed.js');
        $this->assertFileNotExists($test_file);
        $this->assertFileExists($test_file_renamed);
    }

    public function testRenameAlreadyExists()
    {
        $filesystem = new Filesystem();
        $test_file = self::$testPath . '/to_rename.js';
        $filesystem->write($test_file, 'rename file');

        $filesystem->rename($test_file, 'renamed.js');
        $this->expectException(\InvalidArgumentException::class);
        $filesystem->rename($test_file, 'renamed.js');
    }

    /**
     * @covers filesystem\Filesystem::listDirs
     */
    public function testListDirs(): void
    {
        mkdir(sanitize_filename(self::$testPath . '/list-dir'));
        mkdir(self::$testPath . '/list-dir/addon');
        mkdir(self::$testPath . '/list-dir/dir1');
        mkdir(self::$testPath . '/list-dir/path');

        $filesystem = new Filesystem();
        $test_path = sanitize_filename(self::$testPath . '/list-dir/');

        $this->assertSame(
            [
                sanitize_filename(self::$testPath . '/list-dir/addon'),
                sanitize_filename(self::$testPath . '/list-dir/dir1'),
                sanitize_filename(self::$testPath . '/list-dir/path')
            ],
            $filesystem->listDirs($test_path)
        );

        rmdir(self::$testPath . '/list-dir/addon');
        rmdir(self::$testPath . '/list-dir/dir1');
        rmdir(self::$testPath . '/list-dir/path');

        $this->expectException(\InvalidArgumentException::class);
        $filesystem->listDirs(self::$testPath . '/non-existing-path-to-test');
    }
}
