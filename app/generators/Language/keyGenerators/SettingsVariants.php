<?php

namespace generators\Language\keyGenerators;
use generators\Language\enums\LangvarTypes;

final class SettingsVariants extends \generators\Language\keyGenerators\AbstractKeyGenerator
{
    public static function generate(...$paths): string
    {
        list($subpath, $key, $value) = $paths;

        return LangvarTypes::$SETTINGS_VARIANTS . '::' . (($subpath ? $subpath . '::' : '') . $key . '::' . $value);
    }
}
