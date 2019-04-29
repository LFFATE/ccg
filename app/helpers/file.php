<?php

function sanitize_filename($input)
{
    $input = sanitize_slashes($input);
    $output = '';

    while ($input !== '') {
        if (
            ($prefix = substr($input, 0, 3)) == '../' ||
            ($prefix = substr($input, 0, 2)) == './'
           ) {
            $input = substr($input, strlen($prefix));
        } else if (
            ($prefix = substr($input, 0, 3)) == '/./' ||
            ($prefix = $input) == '/.'
           ) {
            $input = '/' . substr($input, strlen($prefix));
        } else

        if (
            ($prefix = substr($input, 0, 4)) == '/../' ||
            ($prefix = $input) == '/..'
           ) {
            $input = '/' . substr($input, strlen($prefix));
            $output = substr($output, 0, strrpos($output, '/'));
        } else if ($input == '.' || $input == '..') {
            $input = '';
        } else {
            $pos = strpos($input, '/');
            if ($pos === 0) $pos = strpos($input, '/', $pos+1);
            if ($pos === false) $pos = strlen($input);
            $output .= substr($input, 0, $pos);
            $input = (string) substr($input, $pos);
        }
    }

    return $output;
}

function sanitize_slashes($filename)
{
    return str_replace('\\', '/', $filename);
}
