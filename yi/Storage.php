<?php

namespace yi;

use support\Str;

class Storage 
{
    public static function config($option)
    {
        $driver = $option['driver'] ?? config('storage.default');
        $class = config('storage.drivers.' . $driver . '.driver') ?: "\\yi\\storage\\" . Str::studly($driver);
        $option['driver'] = $driver;
        return (new $class)->init($option);
    }
}