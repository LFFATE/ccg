<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

final class HelperFileTest extends TestCase
{
    public function testGetAbsolutePath(): void
    {
        $this->assertEquals(
            '/dir/path/sub/project',
            sanitize_filename('/dir/path/sub/root/../project')
        );

        $this->assertEquals(
            'C:/dir/path/sub/root/project',
            sanitize_filename('C:\dir\path\sub\root/project')
        );

        $this->assertEquals(
            'C:/dir/path/root/',
            sanitize_filename('C:/dir/path/sub/../root/project/../')
        );
    }

    public function testSanitizeSlashes(): void
    {
        $this->assertEquals(
            'C:/dir/path/sub/root/project',
            sanitize_slashes('C:\dir\path\sub\root\project')
        );

        $this->assertEquals(
            '/var/log/access.log',
            sanitize_slashes('/var/log/access.log')
        );
    }
}
