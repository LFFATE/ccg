<?php

namespace generators;

use mediators\AbstractMediator;

abstract class AbstractGenerator {
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
}
