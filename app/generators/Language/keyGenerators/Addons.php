<?php

namespace generators\Language\keyGenerators;
use generators\Language\enums\LangvarTypes;

final class Addons extends \generators\Language\keyGenerators\AbstractKeyGenerator
{
    public static function generate(...$paths): string
    {
        list($key, $addon_id) = $paths;

        return LangvarTypes::$ADDONS . '::' . ($key ? ($key . '::' . $addon_id) : $addon_id);
    }
}
