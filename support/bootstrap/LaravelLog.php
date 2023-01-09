<?php
namespace support\bootstrap;

use Webman\Bootstrap;
use support\Db;
use support\Str;

class LaravelLog implements Bootstrap
{
    public static function start($worker)
    {
        if (!config('app.sql_log')) return;
        Db::listen(function ($query) {
            $sql = $query->sql;
            $bindings = [];
            if ($query->bindings) {
                foreach ($query->bindings as $v) {
                    if (is_numeric($v)) {
                        $bindings[] = $v;
                    } else {
                        $bindings[] = '"' . strval($v) . '"';
                    }
                }
            }
            $execute = Str::replaceArray('?', $bindings, $sql);
            logs()->info('SQL [' . $query->time . ' ms] ' . $execute);
        });
    }
}