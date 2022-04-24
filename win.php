<?php
/**
 * Start file for windows
 */
require_once __DIR__ . '/vendor/autoload.php';

use process\Monitor;
use Workerman\Worker;
use Webman\Config;

ini_set('display_errors', 'on');
error_reporting(E_ALL);

Config::load(config_path(), ['route', 'container']);

$runtime_process_path = runtime_path() . DIRECTORY_SEPARATOR . '/windows';
if (!is_dir($runtime_process_path)) {
    mkdir($runtime_process_path);
}
$process_files = [
    __DIR__ . DIRECTORY_SEPARATOR . 'start.php'
];
$env = $argv[1] ?? 'pro';
foreach (config('process', []) as $process_name => $config) {
    if ((empty($config['env']) || $env != $config['env']) && empty($config['enable'])) continue;
    $file_content = <<<EOF
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Workerman\Worker;
use Webman\Config;

ini_set('display_errors', 'on');
error_reporting(E_ALL);

if (is_callable('opcache_reset')) {
    opcache_reset();
}
    
Config::load(config_path(), ['route', 'container']);

worker_start('$process_name', config('process')['$process_name']);
Worker::runAll();

EOF;

    $process_file = $runtime_process_path . DIRECTORY_SEPARATOR . "start_$process_name.php";
    $process_files[] = $process_file;
    file_put_contents($process_file, $file_content);
}

foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        foreach ($project['process'] ?? [] as $process_name => $config) {
            $file_content = <<<EOF
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Workerman\Worker;
use Webman\Config;

ini_set('display_errors', 'on');
error_reporting(E_ALL);

if (is_callable('opcache_reset')) {
    opcache_reset();
}

Config::load(config_path(), ['route', 'container']);

worker_start("plugin.$firm.$name.$process_name", config("plugin.$firm.$name.process")['$process_name']);
Worker::runAll();

EOF;
            $process_file = $runtime_process_path . DIRECTORY_SEPARATOR . "start_$process_name.php";
            $process_files[] = $process_file;
            file_put_contents($process_file, $file_content);
        }
    }
}

function popen_processes($process_files)
{
    $cmd = "php " . implode(' ', $process_files);
    $descriptorspec = [STDIN, STDOUT, STDOUT];
    $resource = proc_open($cmd, $descriptorspec, $pipes);
    if (!$resource) {
        exit("Can not execute $cmd\r\n");
    }
    return $resource;
}
$monitor = new Monitor(...array_values(config('process.monitor.constructor')));
$resource = popen_processes($process_files);
function g_system_reload(&$resource, $process_files)
{
    echo "system reloading... \n";
    $status = proc_get_status($resource);
    $pid = $status['pid'];
    shell_exec("taskkill /F /T /PID $pid");
    proc_close($resource);
    $resource = popen_processes($process_files);
}
echo "\r\n";
while (1) {
    sleep(1);
    if (trim(file_get_contents(runtime_path() . '\\' . 'signal') == 'WAITING')) {
        file_put_contents(runtime_path() . '\\' . 'signal', 'LOADING');
        g_system_reload($resource, $process_files);
    }
    if ($env == 'dev' && $monitor->checkAllFilesChange()) {
        g_system_reload($resource, $process_files);
    }
}