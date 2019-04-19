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
            get_absolute_path('/dir/path/sub/root/../project')
        );
    }
}
