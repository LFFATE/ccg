<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

use terminal\Terminal;
use filesystem\Filesystem;
use autocomplete\Autocomplete;

final class AutocompleteTest extends TestCase
{
    protected static $sourcePath = __DIR__ . '/sources/autocomplete/cscart/${addon.id}/';
    protected $autocomplete;
    protected $filesystem;
    protected $config;

    public function setUp(): void
    {
        $this->config = new \Config([
        ],
        [
            'filesystem.output_path' => self::$sourcePath,
        ]);
        
        $this->filesystem = new Filesystem();
    }

    public function testCanCreate(): void
    {
        $terminal           = new Terminal();
        $this->autocomplete = new Autocomplete($this->config, $terminal, $this->filesystem);

        $this->assertInstanceOf(Autocomplete::class, $this->autocomplete);
    }

    /**
     * @covers autocomplete\Autocomplete::getAddonsList
     */
    public function testGetAddonsList(): void
    {
        $terminal           = new Terminal();
        $this->autocomplete = new Autocomplete($this->config, $terminal, $this->filesystem);

        $addons_list = $this->autocomplete->getAddonsList();

        $this->assertTrue(in_array('paypal', $addons_list));
        $this->assertTrue(in_array('sd_new_addon', $addons_list));
        $this->assertTrue(in_array('sd_test_addon', $addons_list));
    }

    /**
     * @covers autocomplete\Autocomplete::queueArgument
     */
    public function testCanQueueArgumentSuggestAParam(): void
    {
        $terminal           = new Terminal();
        $this->autocomplete = new Autocomplete($this->config, $terminal, $this->filesystem);

        $result = $this->autocomplete->queueArgument('addon.id', ['great_addon', 'design_improvements']);
        
        $this->assertTrue(in_array('--addon.id', $result));
    }

    /**
     * @covers autocomplete\Autocomplete::queueArgument
     */
    public function testCanQueueArgumentSuggestAValues(): void
    {
        global $argv;
        $argv_backup = $argv;
        $argv = [
            'ccg.php',
            'addon/create',
            '--addon.id',
            '--autocomplete',
            'y',
            '--prev',
            '"--addon.id"',
        ];

        $terminal           = new Terminal();
        $this->autocomplete = new Autocomplete($this->config, $terminal, $this->filesystem);

        $result = $this->autocomplete->queueArgument('addon.id', ['great_addon', 'design_improvements']);
        
        $this->assertTrue(in_array('great_addon', $result));
        $this->assertTrue(in_array('design_improvements', $result));
        unset($result);

        $result = $this->autocomplete->queueArgument('addon.id', function() {
            return ['great_addon', 'design_improvements'];
        });
        $this->assertTrue(in_array('great_addon', $result));
        $this->assertTrue(in_array('design_improvements', $result));

        $argv = $argv_backup;
    }

    /**
     * @covers autocomplete\Autocomplete::combineQueueParam
     */
    public function testCanCombineQueueArgumentAndSuggestFirstParam(): void
    {
        global $argv;
        $argv_backup = $argv;
        $argv = [
            'ccg.php',
            'addon/create',
            '--autocomplete',
            'y',
            '--prev',
            '""',
        ];

        $terminal           = new Terminal();
        $this->autocomplete = new Autocomplete($this->config, $terminal, $this->filesystem);

        $result = $this->autocomplete->combineQueueParam(
            $this->autocomplete->queueArgument('addon.id', ['great_addon', 'design_improvements']),
            $this->autocomplete->queueArgument('addon.scheme', ['3.0', '4.0'])
        );

        $this->assertTrue(in_array('--addon.id', $result));
        unset($result);
        $argv = $argv_backup;
    }

    /**
     * @covers autocomplete\Autocomplete::combineQueueParam
     */
    public function testCanCombineQueueArgumentAndSuggestSecondParamValues(): void
    {
        global $argv;
        $argv_backup = $argv;
        $argv = [
            'ccg.php',
            'addon/create',
            '--addon.id',
            'great_addon',
            '--addon.scheme',
            '--autocomplete',
            'y',
            '--prev',
            '"--addon.scheme"',
        ];

        $terminal           = new Terminal();
        $this->autocomplete = new Autocomplete($this->config, $terminal, $this->filesystem);

        $result = $this->autocomplete->combineQueueParam(
            $this->autocomplete->queueArgument('addon.id', ['great_addon', 'design_improvements']),
            $this->autocomplete->queueArgument('addon.scheme', ['3.0', '4.0'])
        );

        $this->assertTrue(in_array('3.0', $result));
        $this->assertTrue(in_array('4.0', $result));
        unset($result);
        $argv = $argv_backup;
    }
}
