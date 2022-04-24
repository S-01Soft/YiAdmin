<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

$config = [];
foreach (scandir(app_path()) as $dir) {
    if (in_array($dir, ['.', '..'])) continue;
    $file = app_path() . DS . $dir . DS . 'process.php';
    $info_file = app_path() . DS . $dir . DS . 'info.ini';
    if (!file_exists($file) || !file_exists($info_file)) continue;
    $info = parse_ini_file($info_file);
    if (!$info) continue;
    if (empty($info['status'])) continue;
    $array = include $file;
    if (empty($array) || !is_array($array)) continue;
    foreach ($array as $key => $v) {
        if (!empty($v['enable'])) $config[$key] = $v;
    }
}

$defaults = [
    'monitor' => [
        'enable' => true,
        'handler' => process\Monitor::class,
        'reloadable' => false,
        'constructor' => [
            'monitor_dir' => [
                app_path(), 
                config_path(),
                base_path() . '/process',
                base_path() . '/support',
                base_path() . '/resource',
                base_path() . '/.env',
                base_path() . '/yi',
            ],
            // Files with these suffixes will be monitored
            'monitor_extensions' => [
                'php', 'html', 'htm', 'env'
            ]
        ]
    ],
    'restart' => [
        'env' => DS == '/' ? 'dev' : '',
        'handler' => process\Restart::class,
        'reloadable' => false,
    ]
];

return array_merge($defaults, $config);