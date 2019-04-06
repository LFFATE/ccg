<?php

namespace generators\Language\keyGenerators;
use generators\Language\enums\LangvarTypes;

final class ProfileFields extends \generators\Language\keyGenerators\AbstractKeyGenerator
{
    public static function generate(...$paths): string
    {
        list($subpath, $key) = $paths;

        return LangvarTypes::$PROFILE_FIELDS . '::' . ($subpath ? ($subpath . '::' . $key) : $key);
    }
}
