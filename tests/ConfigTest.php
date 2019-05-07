<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testCanBeCreatedFromValidArgs(): void
    {
        $arguments = [
            'generator' => 'test'
        ];

        $config = new Config($arguments);

        $this->assertInstanceOf(
            Config::class,
            $config
        );
    }

    public function testCanParseArgv(): void
    {
        $arguments = [
            'generator' => 'addonXml',
            'command'   => 'remove',
            'addon.id'  => 'sd_new',
            'subpath'   => 'cscart',
            'path'      => '/var/path/${subpath}/${addon.id}',
        ];

        $config = new Config($arguments);

        $this->assertEquals('addonXml', $config->get('generator'));
        $this->assertEquals('remove', $config->get('command'));
        $this->assertEquals('sd_new', $config->get('addon.id'));
        $this->assertEquals('/var/path/cscart/sd_new', $config->get('path'));
    }

    public function testCanSetValue(): void
    {
        $config = new Config([]);
        $config->set('string', 'value');
        $config->set('array', ['key' => 'val']);

        $this->assertEquals($config->get('string'), 'value');
        $this->assertEquals($config->get('array'), ['key' => 'val']);
    }

    public function testCanGetOr(): void
    {
        $config = new Config([]);
        $config->set('string', 'value');

        $this->assertEquals(
            'value',
            $config->getOr('string', 's')
        );

        $this->assertEquals(
            'value',
            $config->getOr('s', 'string')
        );

        $this->assertEquals(
            'value',
            $config->getOr('findSome', 's', 'string')
        );

        $this->assertEquals(
            null,
            $config->getOr('not-found', 'checko-for-null')
        );
    }

    public function testCanGetAll(): void
    {
        $config = new Config([
            'generator' => 'generator',
            'command'   => 'command',
            'addon'     => 'sd_addon',
            'item'      => 'itemname',
            'remove'    => 'true',
            'check'     => 'exists'
        ]);

        $config->set('string', 'value');

        $this->assertEquals(
            array_filter($config->getAll(), function($key) {
                return $key !== 'path'; // Don't test path item
            }, ARRAY_FILTER_USE_KEY),
            [
                'generator' => 'generator',
                'command' => 'command',
                'addon' => 'sd_addon',
                'item' => 'itemname',
                'remove' => 'true',
                'check' => 'exists',
                'string' => 'value'
            ]
        );
    }
}
