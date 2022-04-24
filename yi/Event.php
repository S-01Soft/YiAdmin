<?php

namespace yi;

class Event 
{
    protected $listeners = [];

    protected $binds = [];

    public function listen(string $event, $listener)
    {
        if (isset($this->listeners[$event])) {
            if (!in_array($listener, $this->listeners[$event])) $this->listeners[$event][] = $listener;
        }
        else $this->listeners[$event] = [$listener];
    }

    public function listenEvents($events)
    {
        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->listen($event, $listener);
            }
        }
        return $this;
    }

    public function bind(string $event, $listener)
    {
        if (isset($this->binds[$event])) {
            if (!in_array($listener, $this->binds[$event])) $this->binds[$event][] = $listener;
        }
        else $this->binds[$event] = [$listener];
    }

    public function bindEvents($events)
    {
        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->bind($event, $listener);
            }
        }
        return $this;
    }

    public function dispatch(string $event, $params = null, $once = false)
    {
        $listeners = $this->getEventList();
        if (!isset($listeners[$event])) return;
        foreach ($listeners[$event] as $listener) {
            if (is_object($listener)) $listener($params);
            else {
                if (strpos($listener, '@')) list($class, $method) = explode('@', $listener);
                else {
                    $class = $listener;
                    $method = 'handle';
                }
                app($class)->{$method}($params);
            }
        }
    }

    public function subscribe($subscribers)
    {
        foreach ($subscribers as $subscriber) {
            if (is_string($subscriber)) {
                if (!class_exists($subscriber)) continue;
                $subscriber = app($subscriber);
            }

            if (method_exists($subscriber, 'subscribe')) {
                $subscriber->subscribe($this);
            } else {
                $this->observe($subscriber);
            }
        }

        return $this;
    }
    
    public function observe($observer, string $prefix = '')
    {
        $reflect = new \ReflectionClass($observer);
        $methods = $reflect->getMethods(\ReflectionMethod::IS_PUBLIC);

        if (empty($prefix) && $reflect->hasProperty('eventPrefix')) {
            $reflectProperty = $reflect->getProperty('eventPrefix');
            $reflectProperty->setAccessible(true);
            $prefix = $reflectProperty->getValue($observer);
        }

        foreach ($methods as $method) {
            $name = $method->getName();
            if (0 === strpos($name, 'on')) {
                $this->listen($prefix . substr($name, 2), get_class($observer) . '@' . $name);
            }
        }
        
        return $this;
    }

    public function getEventList()
    {
        return array_merge_recursive($this->listeners, $this->binds);
    }

    public function getBinds()
    {
        return $this->binds;
    }

    public function getListeners()
    {
        return $this->listeners;
    }

    public function clear()
    {
        $this->binds = [];
    }
}