<?php
declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

final class HelperStringTest extends TestCase
{
    /**
     * @covers ::explode_by_new_line
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

    /**
     * @covers ::parse_to_readable
     */
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

    /**
     * @covers ::to_camel_case
     */
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
    
    /**
     * @covers ::to_studly_caps
     */
    public function testToStudlyCaps(): void
    {
        $this->assertSame(
            'RemoveItem',
            to_studly_caps('remove-item')
        );
        $this->assertSame(
            'RemoveSettingsItem',
            to_studly_caps('remove-settings-item')
        );
        $this->assertSame(
            'Remove',
            to_studly_caps('remove')
        );
    }

    /**
     * @covers ::to_lower_case
     */
    public function testToLowerCase(): void
    {
        $this->assertSame('remove-item', to_lower_case('removeItem'));
        $this->assertSame('remove-item', to_lower_case('RemoveItem'));
    }

    /**
     * @covers ::arguments
     */
    public function testArguments()
    {
        $argv_test = 'ccg.php addon/create --addon.id "new_addon" --langvar "say my name \"Daniel\"" --cur "" --developer mikhail ddfgd --test';

        $arguments = arguments($argv_test);
        $this->assertSame('new_addon', $arguments['addon.id']);
        $this->assertSame(true, $arguments['test']);
        $this->assertSame('say my name \"Daniel\"', $arguments['langvar']);
        $this->assertSame('mikhail', $arguments['developer']);

        $argv_test = 'ccg.php addon/create --addon.id new_addon';
        $arguments = arguments($argv_test);
        $this->assertSame('new_addon', $arguments['addon.id']);

        $argv_test = 'ccg.php addon/create --addon.id new addon';
        $arguments = arguments($argv_test);
        $this->assertSame('new', $arguments['addon.id']);

        $argv_test = 'ccg.php addon/create --test --addon.id new addon';
        $arguments = arguments($argv_test);
        $this->assertSame(true, $arguments['test']);
        $this->assertSame('new', $arguments['addon.id']);

        $argv_test = 'ccg.php addon/create --test-option true';
        $arguments = arguments($argv_test);
        $this->assertSame('true', $arguments['test-option']);

        $argv_test = 'ccg.php addon/create --test-option settings-item';
        $arguments = arguments($argv_test);
        $this->assertSame('settings-item', $arguments['test-option']);

        $argv_test = 'ccg.php addon/create --test_option true --one-char-value y';
        $arguments = arguments($argv_test);
        $this->assertSame('true', $arguments['test_option']);
        $this->assertSame('y', $arguments['one-char-value']);
    }
}
