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
        $this->fileGenerators[] = [
            'type'      => $className,
            'generator' => new FileGenerator($generator, $this->filesystem)
        ];

        return $this;
    }

    public function removeGenerator(string $className) {
        $inputClassName = (new \ReflectionClass($className))->getShortName();

        $this->fileGenerators = array_filter($this->fileGenerators, function($fileGenerator) use ($inputClassName) {
            $currentClassName = (new \ReflectionClass($fileGenerator['generator']->extract()))->getShortName();

            return $inputClassName !== $currentClassName;
        });

        return $this;
    }

    public function find(string $className): AbstractFileGenerator
    {
        $inputClassName = (new \ReflectionClass($className))->getShortName();
        $found = array_filter($this->fileGenerators, function($fileGenerator) use ($inputClassName) {
            $currentClassName = (new \ReflectionClass($fileGenerator['generator']->extract()))->getShortName();
            return $inputClassName === $currentClassName;
        });

        if (!empty($found)) {
            return (current($found))['generator'];
        }

        throw new \InvalidArgumentException('There is no file generator for ' . $className);
    }

    public function filter(string $className)
    {
        $inputClassName = (new \ReflectionClass($className))->getShortName();
        $filtered = new $this($this->filesystem);
        array_walk($this->fileGenerators, function($fileGenerator) use ($filtered, $inputClassName) {
            $currentClassName = (new \ReflectionClass($fileGenerator['generator']->extract()))->getShortName();
            if ($inputClassName === $currentClassName) {
                $filtered->addGenerator($fileGenerator['generator']->extract());
            }
        });

        if (!$filtered->isEmpty()) {
            return $filtered;
        }
        
        throw new \InvalidArgumentException('There is no file generators for ' . $className);
    }

    public function excluding(string $className) {
        return (clone $this)->removeGenerator($className);
    }

    public function including(AbstractGenerator $generator) {
        return (clone $this)->addGenerator($generator);
    }

    public function each(callable $callback)
    {
        $generatorslist = array_column($this->fileGenerators, 'generator');
        array_walk($generatorslist, $callback);

        return $this;
    }

    public function readFromTemplate()
    {
        array_walk($this->fileGenerators, function($fileGenerator) {
            $fileGenerator['generator']->readFromTemplate();
        });

        return $this;
    }

    public function read()
    {
        array_walk($this->fileGenerators, function($fileGenerator) {
            $fileGenerator['generator']->read();
        });

        return $this;
    }

    public function exists(): bool
    {
        return array_reduce($this->fileGenerators, function($exists, $fileGenerator) {
            return $exists && $fileGenerator['generator']->exists();
        }, true);
    }

    public function remove()
    {
        array_walk($this->fileGenerators, function($fileGenerator) {
            $fileGenerator['generator']->remove();
        });

        return $this;
    }

    public function throwIfExists(string $message = '')
    {
        array_walk($this->fileGenerators, function($fileGenerator) use ($message) {
            $fileGenerator['generator']->throwIfExists($message);
        });

        return $this;
    }

    public function throwIfNotExists(string $message = '')
    {
        array_walk($this->fileGenerators, function($fileGenerator) use ($message) {
            $fileGenerator['generator']->throwIfNotExists($message);
        });

        return $this;
    }
    
    public function write()
    {
        array_walk($this->fileGenerators, function($fileGenerator) {
            $fileGenerator['generator']->write();
        });

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->fileGenerators);
    }
}
