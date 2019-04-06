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

        $Config = new Config($argsArray);

        $this->assertInstanceOf(
            Config::class,
            $Config
        );
    }

    public function testCanParseArgv(): void
    {
        $argsArray = [
            '_tools/ccg/generator.php',
            'addonXml',
            'remove',
            'addon=sd_new'
        ];

        $Config = new Config($argsArray);

        $this->assertEquals(
            $Config->get('generator'),
            'addonXml'
        );

        $this->assertEquals(
            $Config->get('command'),
            'remove'
        );

        $this->assertEquals(
            $Config->get('addon'),
            'sd_new'
        );
    }
    public function testCanSetValue(): void
    {
        $Config = new Config([]);
        $Config->set('string', 'value');
        $Config->set('array', ['key' => 'val']);

        $this->assertEquals(
            $Config->get('string'),
            'value'
        );

        $this->assertEquals(
            $Config->get('array'),
            ['key' => 'val']
        );
    }

    public function testCanGetOr(): void
    {
        $Config = new Config([]);
        $Config->set('string', 'value');

        $this->assertEquals(
            $Config->getOr('string', 's'),
            'value'
        );

        $this->assertEquals(
            $Config->getOr('s', 'string'),
            'value'
        );

        $this->assertEquals(
            $Config->getOr('findSome', 's', 'string'),
            'value'
        );
    }

    public function testCanGetAll(): void
    {
        $Config = new Config([
            '',
            'generator',
            'command',
            'addon=sd_addon',
            'item=itemname',
            'remove=true',
            'check=exists'
        ]);

        $Config->set('string', 'value');

        $this->assertEquals(
            array_filter($Config->getAll(), function($key) {
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
