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

    public function testCanForceOutput(): void
    {

        $this->terminal->echo('string', true);
        $this->terminal->success('string', true);
        $this->terminal->warning('string', true);
        $this->terminal->info('string', true);
        $this->terminal->error('string', true);

        ob_start();
        $this->terminal->forceOutput();

        $this->assertEquals(
            ob_get_contents(),
            $this->terminal->echo('string', false, true) . PHP_EOL .
            $this->terminal->success('string', false, true) . PHP_EOL .
            $this->terminal->warning('string', false, true) . PHP_EOL .
            $this->terminal->info('string', false, true) . PHP_EOL .
            $this->terminal->error('string', false, true) . PHP_EOL
        );
        ob_end_clean();
    }

    public function testCanWriteToBuffer(): void
    {

        $this->terminal->echo('string', true);
        $this->terminal->addBuffer('lorem ipsum');

        $this->assertEquals(
            $this->terminal->getBuffer(),
            'string' . PHP_EOL . 'lorem ipsum'
        );
    }

    public function testSuccess(): void
    {

        $this->assertEquals(
            $this->terminal->success('string', false, true),
            "\e[32m" . 'string' . "\e[0m"
        );

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
        $this->assertEquals(
            $this->terminal->warning('string', false, true),
            "\e[43m" . 'string' . "\e[0m"
        );

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
        $this->assertEquals(
            $this->terminal->error('string', false, true),
            "\e[1;31m" . 'string' . "\e[0m"
        );

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
        $this->assertEquals(
            $this->terminal->info('string', false, true),
            "\e[46m" . 'string' . "\e[0m"
        );

        ob_start();
        $this->terminal->info('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[46m" . 'string' . "\e[0m" . PHP_EOL
        );

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
