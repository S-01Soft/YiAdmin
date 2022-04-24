<?php

namespace process;

use Workerman\Timer;
use Workerman\Worker;

class Restart 
{
    public function __construct()
    {
        if (DS == '\\') return; 
        if (!Worker::getAllWorkers()) {
            return;
        }
        if (Worker::$daemonize) return;
        $disable_functions = explode(',', ini_get('disable_functions'));
        if (in_array('exec', $disable_functions, true)) {
            echo "\nMonitor file change turned off because exec() has been disabled by disable_functions setting in " . PHP_CONFIG_FILE_PATH . "/php.ini\n";
        } else {
            if (!Worker::$daemonize) {
                Timer::add(1, function () {
                    if (\yi\System::status() == 'WAITING') posix_kill(posix_getppid(), SIGUSR1);
                });
            }
        }
    }

}