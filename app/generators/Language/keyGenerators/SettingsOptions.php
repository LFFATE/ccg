<?php

namespace generators\Language\keyGenerators;
use generators\Language\enums\LangvarTypes;

final class SettingsOptions extends \generators\Language\keyGenerators\AbstractKeyGenerator
{
    public static function generate(...$paths): string
    {
        list($subpath, $key) = $paths;

        return LangvarTypes::$SETTINGS_OPTIONS . '::' . ($subpath ? ($subpath . '::' . $key) : $key);
    }
}
