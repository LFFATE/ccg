<?php
declare(strict_types=1);
include 'autoloader.php';

use Spatie\Snapshots\MatchesSnapshots;

use PHPUnit\Framework\TestCase;
use filesystem\Filesystem;
use terminal\Terminal;
use autocomplete\Autocomplete;

final class CcgTest extends TestCase
{
    use MatchesSnapshots;
    
    protected static $tmpPath = __DIR__ . '/tmp/ccg/${addon.id}/';
    /**
     * generate addon to this output path for control sources 
     * (snapshot of working state)
     * and comare it by this tests
     */
    protected static $snapshotPath = __DIR__ . '/sources/ccg/${addon.id}/';
    protected $filesystem;
    protected $config;
    protected $terminal;

    public function setUpEnvAndGenerate(array $argv_custom): void
    {
        global $argv;
        require ROOT_DIR . 'config/defaults.php';
        $argv_backup    = $argv;
        $argv           = $argv_custom;
        
        $defaults_normalized = flat_array_with_prefix($defaults);

        $this->filesystem           = new Filesystem();
        $this->terminal             = new Terminal();
        $this->config               = new Config($this->terminal->getArguments(), $defaults_normalized);
        $this->filesystem           = new Filesystem();

        $autocomplete = new Autocomplete(
            $this->config,
            $this->terminal,
            $this->filesystem
        );

        $ccg = new Ccg(
            $this->config,
            $this->terminal,
            $this->filesystem,
            $autocomplete
        );

        $ccg->generate();
        $argv = $argv_backup;
    }

    public function testAddonXmlGeneration(): void
    {
        $tmpPath    = static::$tmpPath;
        $argv       = [
            'ccg.php',
            'addon-xml/create',
            '--filesystem.output_path',
            "\"$tmpPath\"",
        ];

        ob_start();
        $this->setUpEnvAndGenerate($argv);
        $addonXmlContent = $this->filesystem->read(
            $this->config->get('filesystem.output_path') . 'app/addons/' . $this->config->get('addon.id') . '/addon.xml'
        );
        $this->assertMatchesSnapshot($addonXmlContent);
        ob_end_clean();
    }

    public function testAddonGeneration(): void
    {
        $tmpPath    = static::$tmpPath;
        $argv       = [
            'ccg.php',
            'addon/create',
            '--filesystem.output_path',
            "\"$tmpPath\"",
        ];

        ob_start();
        $this->setUpEnvAndGenerate($argv);
        $addonXmlContent = $this->filesystem->read(
            $this->config->get('filesystem.output_path') . 'app/addons/' . $this->config->get('addon.id') . '/addon.xml'
        );
        $readmeContent = $this->filesystem->read(
            $this->config->get('filesystem.output_path') . 'app/addons/' . $this->config->get('addon.id') . '/README.md'
        );
        $languageContent = $this->filesystem->read(
            $this->config->get('filesystem.output_path') . 'var/langs/' . $this->config->get('addon.default_language') . '/addons/' . $this->config->get('addon.id') . '.po'
        );
        $this->assertMatchesSnapshot($addonXmlContent);
        $this->assertMatchesSnapshot($readmeContent);
        $this->assertMatchesSnapshot($languageContent);
        ob_end_clean();
        echo $this->config->get('filesystem.output_path') . 'var/langs/' . $this->config->get('default_language') . '/addons/' . $this->config->get('addon.id') . '.po';
    }

    public function tearDown(): void
    {
        $this->filesystem->delete(__DIR__ . '/tmp/ccg');
    }
}
