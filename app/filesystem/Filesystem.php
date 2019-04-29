<?php

namespace filesystem;

final class Filesystem
{
    public function exists(string $raw_filename)
    {
        $filename = sanitize_filename($raw_filename);

        return file_exists($filename);
    }

    public function read(string $raw_filename)
    {
        $filename = sanitize_filename($raw_filename);

        return @file_get_contents($filename);
    }

    public function delete(string $filename): bool
    {
        $filename_clean = sanitize_filename($filename);

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

    public function write(string $raw_filename, string $data)
    {
        $filename = sanitize_filename($raw_filename);
        $pathinfo = pathinfo($filename);
        if (!file_exists($pathinfo['dirname'])) {
            mkdir($pathinfo['dirname'], 0777, true);
        }

        return false !== file_put_contents($filename, $data);
    }

    /**
     * @param string $raw_filename - full filename to be renamed
     * @param string $new_name - new name of the file, relative to $filename path
     * 
     * @return bool|string
     */
    public function rename(string $raw_filename, string $new_name)
    {
        $filename = sanitize_filename($raw_filename);
        if (!$this->exists($filename)) {
            throw new \InvalidArgumentException("File $filename doesn't exists");
        }
        
        $pathinfo = pathinfo($filename);
        $new_filename = sanitize_filename($pathinfo['dirname'] . '/' . $new_name);

        if ($this->exists($new_filename)) {
            throw new \InvalidArgumentException("File $new_filename already exists");
        }
    
        if (rename($filename, $new_filename)) {
            return $new_filename;
        } else {
            return false;
        }
    }
}
