<?php

namespace controllers;

use generators\AddonXml\AddonXmlGenerator;
use generators\Language\LanguageGenerator;
use generators\Readme\ReadmeGenerator;
use generators\FileGenerator;
use terminal\Terminal;
use filesystem\Filesystem;
use mediators\GeneratorMediator;
use \Config;

class Help extends AbstractController
{
    private $config;
    private $terminal;
    private $filesystem;
    private static $allowedMethods = [
        'index',
    ];

    use HelpTrait;

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

    /**
     * @inheritdoc
     */
    public static function getAllowedMethods(): array
    {
        return self::$allowedMethods;
    }

    /**
     * help:
     * Use for more information:
     * php ccg.php <generator> help
     * allowed generators: addon, addon-xml
     */
    public function index()
    {
        $this->help();
    }
}
