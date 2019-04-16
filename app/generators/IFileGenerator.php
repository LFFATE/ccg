<?php

namespace generators;

interface IFileGenerator {
    public function read();
    public function readFromTemplate();
    public function write();
    public function exists(): bool;
    public function throwIfExists(string $message = '');
    public function throwIfNotExists(string $message = '');
    public function remove();
}
