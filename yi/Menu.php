<?php
namespace yi;

use support\Db;

class Menu 
{
    static $instance;

    public static function __callStatic($name, $args)
    {
        $instance = static::getInstance();
        return $instance->$name(...$args);
    }
    
    public static function getInstance()
    {
        if (!static::$instance) static::$instance = new MenuInstance;
        return static::$instance;
    }
}

class MenuInstance
{
    protected $scene = 'admin';

    protected function getDbName($scene = 'admin')
    {   
        return config("auth.$scene")['auth_rule'];
    }

    public function install($module, $scene = 'admin', $dir = 'admin')
    {
        return run_command('menu', "--name=$module --scene=$scene --dir=$dir");
    }

    public function uninstall($module, $scene = 'admin')
    {
        Db::table($this->getDbName($scene))->where('app', $module)->delete();
    }

    public function enable($module, $scene = 'admin')
    {
        Db::table($this->getDbName($scene))->where('app', $module)->update(['status' => 1]);
    }

    public function disable($module, $scene = 'admin')
    {
        Db::table($this->getDbName($scene))->where('app', $module)->update(['status' => 0]);
    }

}