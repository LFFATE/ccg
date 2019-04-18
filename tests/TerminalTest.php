<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

use terminal\Terminal;

final class TerminalTest extends TestCase
{
    private $Terminal;

    protected function setUp(): void
    {
        $this->Terminal = new Terminal();
    }

    /**
     * @covers terminal\Terminal::echo
     */
    public function testCanOutputToTerminal(): void
    {
        ob_start();
        $this->Terminal->echo('string');

        $this->assertEquals(
            ob_get_contents(),
            'string' . PHP_EOL
        );

        ob_end_clean();
    }

    public function testCanForceOutput(): void
    {

        $this->Terminal->echo('string', true);
        $this->Terminal->success('string', true);
        $this->Terminal->warning('string', true);
        $this->Terminal->info('string', true);
        $this->Terminal->error('string', true);

        ob_start();
        $this->Terminal->forceOutput();

        $this->assertEquals(
            ob_get_contents(),
            $this->Terminal->echo('string', false, true) . PHP_EOL .
            $this->Terminal->success('string', false, true) . PHP_EOL .
            $this->Terminal->warning('string', false, true) . PHP_EOL .
            $this->Terminal->info('string', false, true) . PHP_EOL .
            $this->Terminal->error('string', false, true) . PHP_EOL
        );
        ob_end_clean();
    }

    public function testCanWriteToBuffer(): void
    {

        $this->Terminal->echo('string', true);
        $this->Terminal->addBuffer('lorem ipsum');

        $this->assertEquals(
            $this->Terminal->getBuffer(),
            'string' . PHP_EOL . 'lorem ipsum'
        );
    }

    public function testSuccess(): void
    {

        $this->assertEquals(
            $this->Terminal->success('string', false, true),
            "\e[32m" . 'string' . "\e[0m"
        );

        ob_start();
        $this->Terminal->success('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[32m" . 'string' . "\e[0m" . PHP_EOL
        );

        ob_end_clean();
    }

    public function testWarning(): void
    {
        $this->assertEquals(
            $this->Terminal->warning('string', false, true),
            "\e[43m" . 'string' . "\e[0m"
        );

        ob_start();
        $this->Terminal->warning('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[43m" . 'string' . "\e[0m" . PHP_EOL
        );

        ob_end_clean();
    }

    public function testError(): void
    {
        $this->assertEquals(
            $this->Terminal->error('string', false, true),
            "\e[1;31m" . 'string' . "\e[0m"
        );

        ob_start();
        $this->Terminal->error('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[1;31m" . 'string' . "\e[0m" . PHP_EOL
        );

        ob_end_clean();
    }

    public function testInfo(): void
    {
        $this->assertEquals(
            $this->Terminal->info('string', false, true),
            "\e[46m" . 'string' . "\e[0m"
        );

        ob_start();
        $this->Terminal->info('string');

        $this->assertEquals(
            ob_get_contents(),
            "\e[46m" . 'string' . "\e[0m" . PHP_EOL
        );

        ob_end_clean();
    }
}
