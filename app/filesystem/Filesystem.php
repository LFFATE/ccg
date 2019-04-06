<?php

namespace filesystem;

final class Filesystem
{
    public function exists(string $filename)
    {
        return file_exists($filename);
    }

    public function read(string $filename)
    {
        return @file_get_contents($filename);
    }

    public function delete(string $filename): bool
    {
        return unlink($filename);
    }

    public function write(string $filename, string $data)
    {
        $pathinfo = pathinfo($filename);
        if (!file_exists($pathinfo['dirname'])) {
            mkdir($pathinfo['dirname'], 0777, true);
        }

        return false !== file_put_contents($filename, $data);
    }
}
