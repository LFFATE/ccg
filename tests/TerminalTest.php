<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

use terminal\Terminal;

final class TerminalTest extends TestCase
{
    private $terminal;
    const EOL = "\n";

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
            'string' . $this->terminal::EOL
        );

        ob_end_clean();
    }

    public function testSuccess(): void
    {
        ob_start();
        $this->terminal->success('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[32m" . 'string' . "\e[0m" . $this->terminal::EOL
        );

        ob_end_clean();
    }

    public function testWarning(): void
    {
        ob_start();
        $this->terminal->warning('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[43m" . 'string' . "\e[0m" . $this->terminal::EOL
        );

        ob_end_clean();
    }

    public function testError(): void
    {
        ob_start();
        $this->terminal->error('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[1;31m" . 'string' . "\e[0m" . $this->terminal::EOL
        );

        ob_end_clean();
    }

    public function testInfo(): void
    {
        ob_start();
        $this->terminal->info('string');

        $this->assertEquals(
            "\e[46m" . 'string' . "\e[0m" . $this->terminal::EOL,
            ob_get_contents()
        );

        ob_end_clean();
    }

    public function testDiff(): void
    {
        ob_start();
        $this->terminal->diff(<<<EOD
regular line
-removed line
+added line
the end
EOD
        );

        $this->assertEquals(
            'regular line' . $this->terminal::EOL .
            "\e[1;31m" . '-removed line' . "\e[0m" . $this->terminal::EOL .
            "\e[32m" . '+added line' . "\e[0m" . $this->terminal::EOL .
            'the end' . $this->terminal::EOL,
            ob_get_contents()
        );

        ob_end_clean();
    }

    /**
     * @covers terminal\Terminal::confirm
     */
    public function testConfirm(): void
    {
        ob_start();
        $is_executed = false;
        $stream  = fopen('php://memory', 'rw');
        $memory_terminal = new Terminal($stream, $stream);
        fwrite($stream, "Y\n");
        $memory_terminal->confirm(function() use (&$is_executed) {
            $is_executed = true;
        });
        fclose($stream);

        $this->assertSame(true, $is_executed);

        $is_cancelled = false;
        $is_success = false;
        $stream  = fopen('php://memory', 'rw');
        $memory_terminal = new Terminal($stream, $stream);
        fwrite($stream, "N\n");
        $memory_terminal->confirm(
            function() use (&$is_success) {
                $is_success = true;
            },
            function() use (&$is_cancelled) {
                $is_cancelled = true;
            }
        );
        fclose($stream);

        $this->assertSame(false, $is_success);
        $this->assertSame(true, $is_cancelled);
        ob_end_clean();
    }

    /**
     * @covers terminal\Terminal::requestSetting
     */
    public function testRequestSetting(): void
    {
        ob_start();
        $expected_result = false;
        $stream  = fopen('php://memory', 'rw');
        $memory_terminal = new Terminal($stream, $stream);
        fwrite($stream, "result\n");
        $memory_terminal->requestSetting('addon.id', function($result) use (&$expected_result) {
            $expected_result = $result;
        }, 'default');
        fclose($stream);

        $this->assertSame('result', $expected_result);

        $stream  = fopen('php://memory', 'rw');
        $memory_terminal = new Terminal($stream, $stream);
        fwrite($stream, '');
        $memory_terminal->requestSetting('addon.id', function($result) use (&$expected_result) {
            $expected_result = $result;
        }, 'default');
        fclose($stream);

        $this->assertSame('default', $expected_result);

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
