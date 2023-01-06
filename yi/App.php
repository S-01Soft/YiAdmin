<?php

namespace yi;

use support\Container;

class App 
{
    protected $containers = [];

    public function get($name)
    {
        return $this->containers[$name] ?? Container::get($name);
    }

    public function has($name)
    {
        return isset($this->containers[$name]);
    }

    public function bind($abstract, $class = null, $parameters = [], $single = true)
    {
        if (is_array($abstract)) {
            foreach ($abstract as $item) {
                $this->bind(...$item);
            }
            return $this;
        }
        $container = Container::instance();
        if ($container->has($abstract) && $single) return $this;
        $fn = is_callable($class) ? call_user_func($class, $container) : function($container) use ($class, $parameters) {
            return $container->make($class, $parameters);
        };
        $container->set($abstract, $fn);
        return $this;
    }

    public function container()
    {
        return Container::instance();
    }

    public function make($name, $parameters = [], $single = true)
    {
        if ($single && $this->has($name)) return $this->containers[$name]; 
        $this->containers[$name] = $this->container()->make($name, $parameters);
        return $this->containers[$name];
    }
}