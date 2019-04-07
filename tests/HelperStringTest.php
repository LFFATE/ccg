<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

final class HelperStringTest extends TestCase
{
    /**
     * @covers explode_by_new_line
     */
    public function testExplodeByLines(): void
    {
        $string = <<<EOD
check file
for new
line

and multiline


.fa
EOD;

        $array = explode_by_new_line($string);
        $this->assertEquals(
            [
                'check file',
                'for new',
                'line',
                '',
                'and multiline',
                '',
                '',
                '.fa'
            ],
            $array
        );
    }

    public function testParseToReadable(): void
    {
        $this->assertSame(
            'Make functions pure',
            parse_to_readable('make_functions_pure')
        );
        $this->assertSame(
            'Side effects is bad',
            parse_to_readable('side__effects__is_bad')
        );
        $this->assertSame(
            'Make your code for humans',
            parse_to_readable('make_your_Code_foR_humans')
        );
        $this->assertSame(
            '1lorem ipsum',
            parse_to_readable('1lorem_ipsum')
        );
        $this->assertSame(
            'Make your code for 43 humans',
            parse_to_readable('Make_your_code_for_43_humans')
        );
        $this->assertSame(
            'Side effects is 4bad',
            parse_to_readable('side__effects__is_4bad')
        );
    }

    public function testToCamelCase(): void
    {
        $this->assertSame(
            'removeItem',
            to_camel_case('remove-item')
        );
        $this->assertSame(
            'removeSettingsItem',
            to_camel_case('remove-settings-item')
        );
        $this->assertSame(
            'remove',
            to_camel_case('remove')
        );
    }
}
