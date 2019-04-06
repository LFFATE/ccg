<?php

namespace generators\Language\keyGenerators;

abstract class AbstractKeyGenerator
{
    abstract public static function generate(...$paths): string;
}
