<?php

namespace support\bootstrap;

use Webman\Bootstrap;
use support\Container;

class Event implements Bootstrap
{
    public static function start($worker)
    {
        $eventInstance = app(\yi\Event::class);
        $modules = get_full_module_list();
        foreach ($modules as $name => $info) {
            if (empty($info['status'])) continue;
            $eventFile = app_path() . DS . $name . DIRECTORY_SEPARATOR . 'event.php';
            if (file_exists($eventFile)) {
                $events = include $eventFile;
                if (!empty($events['listen'])) {
                    foreach ($events['listen'] as $name => $items) {
                        foreach ($items as $listener) {
                            $eventInstance->listen($name, $listener);
                        }
                    }
                }
                if (!empty($events['subscribe'])) {
                    $eventInstance->subscribe($events['subscribe']);
                }
            }
        }
    }
}