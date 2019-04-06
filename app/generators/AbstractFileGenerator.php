<?php

namespace generators;

abstract class AbstractFileGenerator {
    private $generator;

    abstract public function read();
    abstract public function write();
    abstract public function exists(): bool;
    abstract public function throwIfExists(string $message = '');
    abstract public function throwIfNotExists(string $message = '');
    abstract public function remove();
}
