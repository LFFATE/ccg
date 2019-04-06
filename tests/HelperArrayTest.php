<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

final class HelperArrayTest extends TestCase
{
    public function testNormalizeArray(): void
    {
        $m_array = [
            'addon' => [
                'id'        => 'sd_addon',
                'version'   => '4.8',
                'sub' => [
                    'item' => 'value'
                ]
            ],
            'filesystem' => [
                'path' => '/'
            ]
        ];

        $flat_array = flat_array_with_prefix($m_array);

        $this->assertArrayHasKey('addon.sub.item', $flat_array);
        $this->assertArrayHasKey('filesystem.path', $flat_array);
        $this->assertEquals(
            'value',
            $flat_array['addon.sub.item']
        );

        $this->assertArrayHasKey('addon.id', $flat_array);
        $this->assertEquals(
            'sd_addon',
            $flat_array['addon.id']
        );
    }
}
