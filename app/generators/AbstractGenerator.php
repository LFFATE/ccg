<?php

namespace generators;

use mediators\AbstractMediator;

abstract class AbstractGenerator {
    protected $variants;

    /**
     * Set generator content from string
     * @param string $content
     */
    abstract public function setContent(string $content);

    /**
     * Get generator result as string
     *
     * @return string
     */
    abstract public function toString(): string;

    abstract public function setMediator(AbstractMediator $mediator): void;

    /**
     * Get full filename
     */
    abstract public function getPath(): string;

    /**
     * Get template path
     */
    abstract public function getTemplateFilename(): string;

    /**
     * Get unique generator key based on params
     */
    public function getKey(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
    
    /**
     * Get variants for setting/option
     * 
     * @return array
     */
    public function getVariants(string $option)
    {
        if (!empty($this->variants[$option])) {
            return $this->variants[$option];
        }

        return [];
    }
}
