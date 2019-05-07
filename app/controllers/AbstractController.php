<?php

namespace controllers;

abstract class AbstractController
{
    protected $config;
    protected $terminal;
    protected $filesystem;
    protected $autocomplete;
    
    /**
     * Returns array of methods that can be requested
     */
    abstract public static function getAllowedMethods(): array;

    /**
     * Get method name from terminal arguments
     * 
     * @return string
     */
    protected function getMethodName(): string
    {
        return $this->config->get('set')
            ? 'set' . ucfirst(to_camel_case($this->config->get('set')))
            : 'remove' . ucfirst(to_camel_case($this->config->get('remove')));
    }
}
