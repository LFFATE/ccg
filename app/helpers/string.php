<?php

/**
 * Explode string by lines
 * @param string $multiline_string - string to be exploded
 *
 * @return array - array of lines
 */
function explode_by_new_line(string $multiline_string): array
{
    return preg_split("/\r\n|\n|\r/", $multiline_string);
}

/**
 * Parses srings like main_settings_section to Main settings section
 */
function parse_to_readable(string $source): string
{
    return ucfirst(
        preg_replace(
            '/_+/',
            ' ',
            mb_strtolower($source)
        )
    );
}

/**
 * Parses strings like addon-xml to addonXml
 * @param string $string 
 * 
 * @return string
 */
function to_camel_case(string $string): string
{
    return preg_replace_callback('/(-(\w+))/', function($matches) {
        return ucfirst($matches[2]);
    }, $string);
}

/**
 * Parse command line like arguments into array
 * 
 * @param string $command
 * 
 * @return array
 */
function arguments(string $command) {
    $arguments = [];

    preg_replace_callback(
        '/--([\w\.\-_]+)\s*("([^"\\\]*(\\\.[^"\\\]*)*)"|[\w\d\.]+)?/ius',
        function($matches) use (&$arguments) {
            $key = $matches[1];
            $value = true;

            switch(count($matches)) {
                case 4:
                case 5:
                    $value = $matches[3];
                break;
                case 3:
                    $value = $matches[2];
                break;
            }
            
            $arguments[$key] = $value;
        },
        // 'ccg.php addon/create --addon.id "new_addon" --langvar "say my name \"Daniel\"" --cur "" --developer mikhail ddfgd --test');
        $command
    );

    return $arguments;
}
