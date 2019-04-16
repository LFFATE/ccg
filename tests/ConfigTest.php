<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testCanBeCreatedFromValidArgs(): void
    {
        $argsArray = [
            '_tools/ccg/generator.php',
            'addonXml',
            'remove',
            'addon=sd_new'
        ];

        $config = new Config($argsArray);

        $this->assertInstanceOf(
            Config::class,
            $config
        );
    }

    public function testCanParseArgv(): void
    {
        $argsArray = [
            '_tools/ccg/generator.php',
            'addonXml',
            'remove',
            'addon.id=sd_new',
            'subpath=cscart',
            'path=/var/path/${subpath}/${addon.id}'
        ];

        $config = new Config($argsArray);

        $this->assertEquals(
            'addonXml',
            $config->get('generator')
        );

        $this->assertEquals(
            'remove',
            $config->get('command')
        );

        $this->assertEquals(
            'sd_new',
            $config->get('addon.id')
        );

        $this->assertEquals(
            '/var/path/cscart/sd_new',
            $config->get('path')
        );
    }
    public function testCanSetValue(): void
    {
        $config = new Config([]);
        $config->set('string', 'value');
        $config->set('array', ['key' => 'val']);

        $this->assertEquals(
            $config->get('string'),
            'value'
        );

        $this->assertEquals(
            $config->get('array'),
            ['key' => 'val']
        );
    }

    public function testCanGetOr(): void
    {
        $config = new Config([]);
        $config->set('string', 'value');

        $this->assertEquals(
            $config->getOr('string', 's'),
            'value'
        );

        $this->assertEquals(
            $config->getOr('s', 'string'),
            'value'
        );

        $this->assertEquals(
            $config->getOr('findSome', 's', 'string'),
            'value'
        );
    }

    public function testCanGetAll(): void
    {
        $config = new Config([
            '',
            'generator',
            'command',
            'addon=sd_addon',
            'item=itemname',
            'remove=true',
            'check=exists'
        ]);

        $config->set('string', 'value');

        $this->assertEquals(
            array_filter($config->getAll(), function($key) {
                return $key !== 'path'; // Don't test path item
            }, ARRAY_FILTER_USE_KEY),
            [
                '' => true,
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
