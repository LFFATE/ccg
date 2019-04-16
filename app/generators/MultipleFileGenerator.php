<?php

namespace generators;

use filesystem\Filesystem;

class MultipleFileGenerator implements IFileGenerator {
    private $fileGenerators = [];
    private $filesystem;

    function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function addGenerator(AbstractGenerator $generator)
    {
        $className = (new \ReflectionClass($generator))->getShortName();
        $this->fileGenerators[$className] = new FileGenerator($generator, $this->filesystem);

        return $this;
    }

    public function removeGenerator(string $className) {
        $inputClassName = (new \ReflectionClass($className))->getShortName();

        $this->fileGenerators = array_filter($this->fileGenerators, function($fileGenerator) use ($inputClassName) {
            $currentClassName = (new \ReflectionClass($fileGenerator->extract()))->getShortName();

            return $inputClassName !== $currentClassName;
        });

        return $this;
    }

    public function find(string $className): AbstractFileGenerator
    {
        $found = @$this->fileGenerators[(new \ReflectionClass($className))->getShortName()];

        if ($found) {
            return $found;
        }

        throw new \InvalidArgumentException('There is no file generator for ' . $className);
    }

    public function excluding(string $className) {
        return (clone $this)->removeGenerator($className);
    }

    public function including(AbstractGenerator $generator) {
        return (clone $this)->addGenerator($generator);
    }

    public function each(callable $callback)
    {
        array_walk($this->fileGenerators, $callback);

        return $this;
    }

    public function readFromTemplate()
    {
        array_walk($this->fileGenerators, function($fileGenerator) {
            $fileGenerator->readFromTemplate();
        });

        return $this;
    }

    public function read()
    {
        array_walk($this->fileGenerators, function($fileGenerator) {
            $fileGenerator->read();
        });

        return $this;
    }

    public function exists(): bool
    {
        return array_reduce($this->fileGenerators, function($exists, $fileGenerator) {
            return $exists && $fileGenerator->exists();
        }, true);
    }

    public function remove()
    {
        array_walk($this->fileGenerators, function($fileGenerator) {
            $fileGenerator->remove();
        });

        return $this;
    }

    public function throwIfExists(string $message = '')
    {
        array_walk($this->fileGenerators, function($fileGenerator) use ($message) {
            $fileGenerator->throwIfExists($message);
        });

        return $this;
    }

    public function throwIfNotExists(string $message = '')
    {
        array_walk($this->fileGenerators, function($fileGenerator) use ($message) {
            $fileGenerator->throwIfNotExists($message);
        });

        return $this;
    }
    
    public function write()
    {
        array_walk($this->fileGenerators, function($fileGenerator) {
            $fileGenerator->write();
        });

        return $this;
    }
}
