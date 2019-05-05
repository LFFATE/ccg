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
            $this->filesystem
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
        // $this->filesystem->write(ROOT_DIR . '/logs/terminal.txt', "gen: $generator; com: $command; prev: $prev; cur: $cur; " . implode(',', $autocompletes));
        
        // if (empty($generator)) {
        //     $autocompletes = $generators;
        // }

        if (class_exists($controllerClass)) {
            $refl = new ReflectionClass($controllerClass);
            $controller = $refl->newInstanceArgs([
                $this->config,
                $this->terminal,
                $this->filesystem
            ]);
            
            $method = $command;
            $is_method_exists = method_exists($controllerClass, $method);
            
            if ($method) {
                $method_autocomplete = $command . 'Autocomplete';
                $autocompletes = method_exists($controllerClass, $method_autocomplete) ? $controller->{$method_autocomplete}($prev, $cur, $arguments) : [];
                // $autocompletes = method_exists($controllerClass, $method) ? $controller->{$method}($prev, $cur) : $this->config->getSystemKeys();
                // $autocompletes = array_merge($autocompletes, $this->config->getSystemKeys());
                $this->filesystem->write(ROOT_DIR . '/logs/terminal.txt', "gen: $generator; method: $method_autocomplete; " . implode(',', $autocompletes) . ':::' . method_exists($controllerClass, $method_autocomplete));
            }
            
            if (empty($autocompletes) && !$is_method_exists) {
                $allowedMethods = $controller::getAllowedMethods();
                $autocompletes = array_map(function($method) use ($generator) {
                    return $generator . '/' . $method;
                }, $allowedMethods);
                // $autocompletes[] = 'aaaaaaaaaaaaaddddd';
            }
        } else {
            $autocompletes = $generators;
        }

        /**
         * @todo return array
         */
        $this->terminal->echo(implode(' ', $autocompletes));
        exit(0);
    }
}
