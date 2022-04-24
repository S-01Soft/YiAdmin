<?php

namespace support\bootstrap;

use Webman\Bootstrap;
use Webman\Channel\Client;
use Workerman\Timer;

class Sync implements Bootstrap
{
    public static function start($worker)
    {
        if (!$worker) return;
        Client::connect('127.0.0.1', env('CHANNEL_PORT'));
        Client::on('SyncData', function($data) use ($worker) {
            list($class, $method, $args, $name) = $data;
            if (empty($name) || in_array($worker->name, (array)$name)) app($class)->$method(...$args);
        });
    }
}