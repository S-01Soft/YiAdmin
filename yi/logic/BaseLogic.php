<?php

namespace yi\logic;

class BaseLogic
{
    public $model;

    public $static;

    protected static $instances = [];

    protected static $_instance = null;

    public function __construct()
    {
        $this->initialize();
    }

    public static function instance($new = true)
    {
        if ($new || !static::$_instance) static::$_instance = new static;
        return static::$_instance;
    }

    public static function newInstance()
    {
        return static::instance();
    }
    
    protected function initialize()
    {
        if ($this->static && class_exists($this->static)) $this->model = new $this->static;
    }
}