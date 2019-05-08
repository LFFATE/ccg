<?php

namespace generators;

use generators\SimpleXMLElementDecorator;

/**
 * Decorator for SimpleXmlElement library with additional functionality
 * @todo: rewrite to support DOMDocument instead of SimpleXmlElement
 */
final class XML extends SimpleXMLElementDecorator
{
    /**
     * Delete linked SimpleXmlElement node when unset XML instance
     */
    function remove()
    {
        unset($this->getSelf()[0]);
    }

    /**
     * Add attribute to xml element
     * If already exists - then update value
     * @param string $name - name of the attribute
     * @param string $value - full value to set
     * @return XML
     */
    public function setAttribute(string $name, string $value): XML
    {
        $attribute = $this->attributes()->{$name};

        if ($attribute) {
            $this->attributes()->{$name} = $value;
        } else {
            $this->addAttribute($name, $value);
        }

        return $this;
    }

    /**
     * Get attribute value by name
     * @param string $name - name of the attribute
     * @return string
     */
    public function getAttributeValue(string $name): string
    {
        $attribute = $this->attributes()->{$name};

        return $attribute;
    }

    /**
     * Find element by name and replace it with new one
     * @param string $name - name of the element
     * @param string $value - content of the element
     * @return XML
     */
    public function setUniqueChild(string $name, string $value): XML
    {
        $element = $this->getSingleElement($name);

        if (!$element) {

            return new XML($this->addChild($name, $value));
        } else {
            ($element->getSelf())[0] = $value;

            return $element;
        }
    }

    /**
     * @return array<XML>|null
     */
    public function getSingleElement(string $name, string $id = '')
    {
        $elements = $this->getElements($name, $id);
        return $elements ? $elements[0] : null;
    }
    /**
     * Get elements by name or by name and id
     * 
     * @param string $name - name of elements to find, 'item' for <item>
     * @param string $id   - id of elements to find, 'main' for <item id="main">
     * 
     * @return array of XML elements
     */
    public function getElements(string $name, string $id = '')
    {
        $elements = $this->xpath('descendant::' . $name . ($id ? '[@id="' . $id . '"]' : ''));

        return array_map(function($element) {
            return new XML($element);
        }, $elements);
    }

    public function getSelf()
    {
        return $this->simpleXMLElement[0];
    }
}
