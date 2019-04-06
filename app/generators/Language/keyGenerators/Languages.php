<?php

namespace generators\Language\keyGenerators;
use generators\Language\enums\LangvarTypes;

final class Languages extends \generators\Language\keyGenerators\AbstractKeyGenerator
{
    public static function generate(...$paths): string
    {
        list($subpath, $key) = $paths;

        return LangvarTypes::$LANGUAGES . '::' . ($subpath ? ($subpath . '.' . $key) : $key);
    }
}
