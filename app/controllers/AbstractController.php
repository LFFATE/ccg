<?php

namespace controllers;

abstract class AbstractController
{
    private $config;
    private $terminal;
    private $filesystem;

    function __construct(
        \Config              $config,
        \Terminal            $terminal,
        \Filesystem          $filesystem
    )
    {
        $this->config               = $config;
        $this->terminal             = $terminal;
        $this->filesystem           = $filesystem;
    }
    
    /**
     * Returns array of methods that can be requested
     */
    abstract public static function getAllowedMethods(): array;
}
