<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

use terminal\Terminal;

final class TerminalTest extends TestCase
{
    private $terminal;

    protected function setUp(): void
    {
        $this->terminal = new Terminal();
    }

    /**
     * @covers terminal\Terminal::echo
     */
    public function testCanOutputToTerminal(): void
    {
        ob_start();
        $this->terminal->echo('string');

        $this->assertEquals(
            ob_get_contents(),
            'string' . PHP_EOL
        );

        ob_end_clean();
    }

    public function testSuccess(): void
    {
        ob_start();
        $this->terminal->success('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[32m" . 'string' . "\e[0m" . PHP_EOL
        );

        ob_end_clean();
    }

    public function testWarning(): void
    {
        ob_start();
        $this->terminal->warning('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[43m" . 'string' . "\e[0m" . PHP_EOL
        );

        ob_end_clean();
    }

    public function testError(): void
    {
        ob_start();
        $this->terminal->error('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[1;31m" . 'string' . "\e[0m" . PHP_EOL
        );

        ob_end_clean();
    }

    public function testInfo(): void
    {
        ob_start();
        $this->terminal->info('string');

        $this->assertEquals(
            "\e[46m" . 'string' . "\e[0m" . PHP_EOL,
            ob_get_contents()
        );

        ob_end_clean();
    }

    public function testAutocomplete(): void
    {
        ob_start();
        $terminal = $this->terminal->autocomplete(['addon.id', 'addon.scheme']);

        $this->assertEquals(
            'addon.id addon.scheme ',
            ob_get_contents()
        );

        $this->assertInstanceOf(Terminal::class, $terminal);

        ob_end_clean();
    }

    /**
     * @covers terminal\Terminal::getArguments
     */
    public function testGetGenerator(): void
    {
        global $argv;
        $argv_backup = $argv;
        $argv = [
            'ccg.php',
        ];

        $terminal = new Terminal();
        $arguments = $terminal->getArguments();
        $this->assertArrayHasKey('generator', $arguments);
        $this->assertSame('', $arguments['generator']);
        
        $argv = [
            'ccg.php',
            '--autocomplete',
            'y'
        ];
        $terminal = new Terminal();
        $arguments = $terminal->getArguments();
        $this->assertArrayHasKey('generator', $arguments);
        $this->assertSame('', $arguments['generator']);

        $argv = $argv_backup;
    }

    /**
     * @covers terminal\Terminal::getArguments
     */
    public function testGetArguments(): void
    {
        global $argv;
        $argv_backup = $argv;
        $argv = [
            'ccg.php',
            'generator/command',
            '--test-option',
            'value'
        ];

        $terminal = new Terminal();
        $arguments = $terminal->getArguments();
        $this->assertArrayHasKey('generator', $arguments);
        $this->assertSame('generator', $arguments['generator']);
        $this->assertArrayHasKey('command', $arguments);
        $this->assertSame('command', $arguments['command']);
        $this->assertArrayHasKey('test-option', $arguments);
        $this->assertSame('value', $arguments['test-option']);

        $argv = $argv_backup;
    }

    /**
     * @covers terminal\Terminal::isAutocomplete
     */
    public function testIsAutocomplete(): void
    {
        global $argv;
        $argv_backup = $argv;
        $argv = [
            'ccg.php',
            'generator/command',
            '--test-option',
            'value',
            '--autocomplete',
            'y'
        ];

        $terminal = new Terminal();
        $this->assertSame(true, $terminal->isAutocomplete());
        $argv = $argv_backup;
    }
}
