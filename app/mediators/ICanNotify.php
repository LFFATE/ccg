<?php

namespace mediators;

interface ICanNotify
{
    public function trigger(string $name, $data = [], $sender = null): void;
}
