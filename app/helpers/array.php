<?php

/**
 * Transform multidimentional array to flat array with prefix
 * @example [addon => [id => 'sd_addon']] => addon.id => 'sd_addon'
 * @param array $array - multidimentional array
 * @param string $prefix
 *
 * @return array
 */
function flat_array_with_prefix($array, $prefix = '')
{
    $result = [];

    foreach ($array as $key => $value)
    {
        $new_key = $prefix . (empty($prefix) ? '' : '.') . $key;

        if (is_array($value)) {
            $result = array_merge($result, flat_array_with_prefix($value, $new_key));
        } else {
            $result[$new_key] = $value;
        }
    }

    return $result;
}
