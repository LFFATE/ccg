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
        $filename_clean = get_absolute_path($filename);

        if (is_dir($filename_clean)) {
            $it = new \RecursiveDirectoryIterator($filename_clean, \RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it,
                        \RecursiveIteratorIterator::CHILD_FIRST);
            foreach($files as $file) {
                if ($file->isDir()){
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            return rmdir($filename_clean);
        } else {
            return unlink($filename_clean);
        }
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
