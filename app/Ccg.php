<?php

use exceptions\FileAlreadyExistsException;
use generators\AddonXml\AddonXmlGenerator;
use generators\Language\LanguageGenerator;
use generators\Readme\ReadmeGenerator;
use generators\FileGenerator;
use enums\Config as EnumConfig;
use terminal\Terminal;
use filesystem\Filesystem;
use mediators\GeneratorMediator;

/**
 * Enter point for all commands
 * @todo Find out a code splitting way for this class
 * It may be a several traits
 */
class Ccg
{
    private $config;
    private $terminal;
    private $filesystem;

    function __construct(
        Config              $config,
        Terminal            $terminal,
        Filesystem          $filesystem
    )
    {
        $this->config               = $config;
        $this->terminal             = $terminal;
        $this->filesystem           = $filesystem;
    }

    public function generate()
    {
        $generator  = $this->config->get(EnumConfig::$GENERATOR);
        $command    = $this->config->get(EnumConfig::$COMMAND) ?: 'index';

        try {
            $controllerClass = 'controllers\\' . $generator;
            $refl = new ReflectionClass($controllerClass);
            $controller = $refl->newInstanceArgs([
                $this->config,
                $this->terminal,
                $this->filesystem
            ]);
        } catch (\Exception $error) {
        }
        
        $controller->{$command}();

        return $this;
    }

    /**
     * addonXml create - $generator and $command
     * if addonXmlCreate not found
     * Then trying to call create(addonXml) $command($generator)
     */
    private function handleCommand($generator, $command)
    {
        $method = $generator . ucfirst($command);

        if (method_exists($this, $method)) {
            return $this->{$method}();
        } elseif(method_exists($this, $command)) {
            return $this->{$command}($generator);
        }

        throw new \BadMethodCallException('There is no such command or generator: ' . $method);
    }
}
