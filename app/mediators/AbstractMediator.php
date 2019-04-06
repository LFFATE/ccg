<?php

namespace mediators;

abstract class AbstractMediator
{
    abstract public function trigger(string $name, $data = [], $sender = null): void;
}
