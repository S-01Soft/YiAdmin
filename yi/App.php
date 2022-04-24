<?php

namespace yi;

use support\Container;

class App 
{
    protected $services = [];

    public function get($name)
    {
        return $this->services[$name] ?? Container::get($name);
    }

    public function bind($name, $class) 
    {
        if (!isset($this->services[$name])) {
            if (is_string($class)) $this->services[$name] = Container::get($class);
            else $this->services[$name] = $class;
        }
        return $this->services[$name];
    }
}