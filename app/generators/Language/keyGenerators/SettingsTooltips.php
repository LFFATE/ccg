<?php

namespace generators\Language\keyGenerators;
use generators\Language\enums\LangvarTypes;

final class SettingsTooltips extends \generators\Language\keyGenerators\AbstractKeyGenerator
{
    public static function generate(...$paths): string
    {
        list($subpath, $key) = $paths;

        return LangvarTypes::$SETTINGS_TOOLTIPS . '::' . ($subpath ? ($subpath . '::' . $key) : $key);
    }
}
