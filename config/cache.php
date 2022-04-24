<?php

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use support\Redis;

return [
    'default' => 'file',
    'adapters' => [
        'file' => [
            'handler' => function() {
                return new FilesystemAdapter('', 0, runtime_path() . DS . 'cache');
            }
        ],
        'redis' => [
            'handler' => function() {
                return new RedisAdapter(Redis::connection()->client());
            }
        ]
    ]
];