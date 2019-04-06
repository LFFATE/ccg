<?php

namespace generators;

abstract class SimpleXMLElementDecorator
{
    /**
     * @var SimpleXMLElement
     */
    protected $simpleXMLElement;

    public function __construct(\SimpleXMLElement $simpleXMLElement)
    {
        $this->simpleXMLElement = $simpleXMLElement;
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->simpleXMLElement, $method), $arguments);
    }

    public function __set($name, $value) {
        $this->simpleXMLElement->$name = $value;
    }

    public function __get($name) {
        return $this->simpleXMLElement->$name;
    }

    public function __isset($name) {
        return isset($this->simpleXMLElement->$name);
    }

    public function __unset($name) {
        unset($this->simpleXMLElement->$name);
    }
}
