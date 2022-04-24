<?php

namespace yi;

class System 
{

    public static function isWin()
    {
        return DIRECTORY_SEPARATOR == '\\';
    }

    public static function write($status)
    {
        mkfile(base_path() . DS . 'runtime' . DS . 'signal', $status);
    }

    public static function status()
    {
        return trim(file_get_contents(runtime_path() . DIRECTORY_SEPARATOR . 'signal'));
    }
    
    public static function reload()
    {
        if (self::isWin() || !\Workerman\Worker::$daemonize) {
            self::write('WAITING');
        } else {
            self::write('RELOADING');
            shell_exec("php " . base_path() . DIRECTORY_SEPARATOR . 'start.php reload');
        }
    }
}