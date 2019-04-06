<?php

class Config
{
    private $params = [];

    function __construct(array $argv, array $defaults = [])
    {
        $this->set('generator', @$argv[1] ?: null);
        $this->set('command', @$argv[2] ?: null);
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
            if (count($e) == 2) {
                $this->set($e[0], $e[1]);
            } // else
        }
    }

    public function set(string $key, $param): void
    {
        $this->params[$key] = $param;
    }

    public function get(string $key)
    {
        return @$this->params[$key] ?: null;
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
