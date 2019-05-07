<?php

namespace autocomplete;

use terminal\Terminal;
use filesystem\Filesystem;
use \Config;

final class Autocomplete
{
    protected $prev;
    protected $cur;
    protected $config;
    protected $terminal;
    protected $filesystem;

    function __construct(
        Config              $config,
        Terminal            $terminal,
        Filesystem          $filesystem
    )
    {
        $this->config               = $config;
        $this->terminal             = $terminal;
        $this->filesystem           = $filesystem;

        $arguments  = $this->terminal->getArguments();
        $this->prev = empty($arguments['prev']) ? '' : $arguments['prev'];
        $this->cur  = empty($arguments['cur']) ? '' : $arguments['cur'];
    }

    /**
     * get list of addons at output path
     * 
     * @return array - array of strings - name of addons
     */
    public function getAddonsList()
    {
        $addonsPath = sanitize_filename(
            $this->config->get('path')
            . $this->config->get('filesystem.output_path_relative') . '../'
        );

        $dirs = $this->filesystem->listDirs($addonsPath);

        return array_map(function($dir) {
            $paths = explode('/', $dir);
            return end($paths);
        }, $dirs);
    }

    /**
     * Suggests a param which still is not used
     * 
     * @param string $argument
     * @param array|callable $values - possible values for this argument (option)
     * or function that returns $values
     * 
     * @return void|array
     */
    public function queueArgument(string $argument, $values = [])
    {
        $arguments  = $this->terminal->getArguments();
        $option     = '--' . $argument;

        if (empty($arguments[$argument])) {
            return [$option];
        }

        if ($this->prev === $option) {
            if (is_callable($values)) {
                return call_user_func($values);
            } else {
                return $values;
            }
        }
    }

    /**
     * Combines suggestions and returns actual suggestion
     * 
     * @param array $queue
     * 
     * @return array
     */
    public function combineQueueParam(...$queue)
    {
        foreach ($queue as $argument) {
            if (is_array($argument)) {
                return $argument;
            }
        }

        return [];
    }
}
