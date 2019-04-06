<?php

namespace generators;

use generators\AbstractGenerator;
use filesystem\Filesystem;

class FileGenerator extends \generators\AbstractFileGenerator
{
    private $generator;
    private $filename;
    private $filesystem;

    function __construct(
        AbstractGenerator $generator,
        Filesystem $filesystem
    )
    {
        $this->generator    = $generator;
        $this->filename     = $this->generator->getPath();
        $this->filesystem   = $filesystem;
    }

    public function read(): FileGenerator
    {
        $this->generator->setContent(
            $this->filesystem->read($this->filename)
        );

        return $this;
    }

    public function write(): FileGenerator
    {
        $this->filesystem->write(
            $this->filename,
            $this->generator->toString()
        );

        return $this;
    }

    public function exists(): bool
    {
        return $this->filesystem->exists($this->filename);
    }

    public function remove(): FileGenerator
    {
        $this->filesystem->delete($this->filename);

        return $this;
    }

    public function throwIfExists(string $message = ''): FileGenerator
    {
        if ($this->exists()) {
            throw new \UnexpectedValueException($message);
        }

        return $this;
    }

    public function throwIfNotExists(string $message = ''): FileGenerator
    {
        if (!$this->exists()) {
            throw new \UnexpectedValueException($message);
        }

        return $this;
    }
}
