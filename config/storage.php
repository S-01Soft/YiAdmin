<?php

return [
    // 默认磁盘
    'default' => getenv('storage.driver') ?: 'local',
    // 磁盘列表
    'drivers' => [
        'local' => [
            'driver' => '',
            'public' => [
                'path' => base_path() . '/public/storage',
                'url' => '/storage',
                'accept' => '*',
            ],
            'private' => [
                'path' => runtime_path() . '/storage', 
                'url' => '/storage',
                'accept' => '*',
            ]
        ]
    ]
];
