<?php

class Config
{
    private $params = [];
    private $systemKeys = [];

    function __construct(array $arguments, array $defaults = [])
    {
        $this->set('path', ROOT_DIR);

        $self = $this;
        array_walk($defaults, function($setting, $key) use ($self) {
            $self->set(
                $key,
                $setting
            );
            $self->systemKeys[] = $key;
        });
        
        foreach($arguments as $key => $value) {
            $this->set($key, $value);
        }
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

    public function getSystemKeys(): array
    {
        return $this->systemKeys;
    }
}
