<?php

use enums\Config as EnumConfig;
use terminal\Terminal;
use filesystem\Filesystem;
use autocomplete\Autocomplete;

/**
 * Enter point for all commands
 */
class Ccg
{
    private $config;
    private $terminal;
    private $filesystem;
    private $autocomplete;

    function __construct(
        Config              $config,
        Terminal            $terminal,
        Filesystem          $filesystem,
        Autocomplete        $autocomplete
    )
    {
        $this->config               = $config;
        $this->terminal             = $terminal;
        $this->filesystem           = $filesystem;
        $this->autocomplete         = $autocomplete;
    }

    public function generate()
    {
        $generator          = $this->config->get(EnumConfig::$GENERATOR);
        $command            = $this->config->get(EnumConfig::$COMMAND) ?: 'index';
        $controllerClass    = 'controllers\\' . $generator;
        
        if (!$generator) {
            throw new \InvalidArgumentException('Provide a command. See php ccg.php help');
        }

        if (!class_exists($controllerClass)) {
            throw new \InvalidArgumentException('Command not recognized. See php ccg.php help');
        }

        $refl = new ReflectionClass($controllerClass);
        $controller = $refl->newInstanceArgs([
            $this->config,
            $this->terminal,
            $this->filesystem,
            $this->autocomplete
        ]);
        
        $allowedMethods = $controller::getAllowedMethods();
        if (in_array($command, $allowedMethods)) {
            $controller->{$command}();
        } else {
            throw new \InvalidArgumentException('Command not recognized. See php ccg.php help');
        }

        return $this;
    }

    public function autocomplete($arguments)
    {
        $generator          = $arguments['generator'];
        $command            = $arguments['command'];
        $prev               = $arguments['prev'];
        $cur                = $arguments['cur'];
        $controllerClass    = 'controllers\\' . $generator;
        
        /**
         * @todo add autogeneration this list
         */
        $generators = [
            'addon',
            'addon-xml',
            'help'
        ];

        $autocompletes = [];

        if (class_exists($controllerClass)) {
            $refl = new ReflectionClass($controllerClass);
            $controller = $refl->newInstanceArgs([
                $this->config,
                $this->terminal,
                $this->filesystem,
                $this->autocomplete
            ]);
            
            $method = $command;
            $is_method_exists = method_exists($controllerClass, $method);
            
            if ($method) {
                $method_autocomplete = $command . 'Autocomplete';
                $autocompletes = method_exists($controllerClass, $method_autocomplete) ? $controller->{$method_autocomplete}($prev, $cur, $arguments) : [];
            }
            
            if (empty($autocompletes) && !$is_method_exists) {
                $allowedMethods = $controller::getAllowedMethods();
                $autocompletes = array_map(function($method) use ($generator) {
                    return to_lower_case($generator . '/' . $method);
                }, $allowedMethods);
            }
        } else {
            $autocompletes = $generators;
        }

        return $autocompletes;
    }
}
