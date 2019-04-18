<?php

class Config
{
    private $params = [];

    function __construct(array $argv, array $defaults = [])
    {
        $this->set('path', ROOT_DIR);

        $self = $this;
        array_walk($defaults, function($setting, $key) use ($self) {
            $self->set(
                $key,
                $setting
            );
        });

        foreach($argv as $arg) {
            $e = explode('=', $arg);
            if (count($e) === 2) {
                $this->set($e[0], $e[1]);
            } else {
                $this->set($e[0], true);
            }
        }

        $this->set('generator', to_camel_case(@$argv[1] ?: ''));
        $this->set('command', @$argv[2] ?: '');
    }

    public function set(string $key, $param): void
    {
        $this->params[$key] = $param;
    }

    public function get(string $key)
    {
        $result = @$this->params[$key] ?: null;
        $config = $this;

        return !is_string($result) ? $result : preg_replace_callback('/(\$\{([\w\.-_]+)\})/', function($matches) use ($config) {
            return $config->get($matches[2]);
        }, $result);
    }

    public function getOr(string ...$keys)
    {
        $value = null;

        foreach($keys as $key) {
            $value = $this->get($key);

            if ($value) {
                return $value;
            }
        }

        return null;
    }

    public function getAll(): array
    {
        return $this->params;
    }
}
