<?php

use Webman\App;
use Webman\Route;
use support\Container;
use support\Str;

$_app_route_init = function() {
    
    $routes = Route::getRoutes();
    $ignore_list = [];
    foreach ($routes as $tmp_route) {
        $ignore_list[$tmp_route->getPath()] = 0;
    }

    $route = function ($uri, $cb, $middlewares) use (&$ignore_list) {
        if (isset($ignore_list[$uri])) {
            return;
        }
        $ignore_list[$uri] = 0;
        if (!Str::endsWith($uri, ']')) {
            Route::any($uri . '.html', $cb)->middleware($middlewares);
            Route::any($uri. '/', $cb)->middleware($middlewares);
        }
        Route::any($uri, $cb)->middleware($middlewares);
        $lower_uri = strtolower($uri);
        if ($lower_uri !== $uri) {
            if (!Str::endsWith($lower_uri, ']')) {
                Route::any($lower_uri . '.html', $cb)->middleware($middlewares);
                Route::any($lower_uri . '/', $cb)->middleware($middlewares);
            }
            Route::any($lower_uri, $cb)->middleware($middlewares);
        }
    };

    $parseRoute = function($uri, $class_name, $action = null) use ($route) {
        if (!class_exists($class_name)) return false;
        $class = new \ReflectionClass($class_name);
        if ($class->isAbstract()) return;
        $middlewares = array_merge(app($class_name)->middlewares, [\yi\middlewares\ActionHook::class]);
        if ($action) $actions = [$action];
        else {
            $actions = [];
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if (in_array($method->name, ['__construct', '__destruct'])) {
                    continue;
                }
                $actions[] = $method->name;
            }
        }
        foreach ($actions as $act) {
            if (!$action) {
                if ($act === 'index') {
                    if (substr($uri, -6) === '/index') {
                        $route(substr($uri, 0, -6), [$class_name, $act], $middlewares);
                    }
                    $route($uri, [$class_name, $act], $middlewares);
                }
                $route($uri . '/' . $act, [$class_name, $act], $middlewares);
            } else {
                $route($uri, [$class_name, $act], $middlewares);
            }
        }
    };

    $module_list = get_full_module_list();

    foreach ($module_list as $name => $info) {
        if (empty($info['status'])) continue;
        $file = app_path() . DS . $name . DS . 'route.php';
        if (!file_exists($file)) continue;
        $list = include_once $file;
        if (empty($list)) continue;
        if (!is_array($list)) continue;
        foreach ($list as $group => $items) {
            if (!is_array($items)) {
                $items = [$items];
            }
            foreach($items as $k => $v) {
                $uri = $group . ($k ? '/' . $k : '');
                
                $array = explode('@', $v);
                if (empty($array)) continue;
                if (count($array) == 1) {
                    $short_class = $array[0];
                    $action = 'index';
                } else [$short_class, $action] = $array;
                if (Str::startsWith($short_class, "\\")) $class_name = $short_class;
                else $class_name = str_replace('/', '\\', 'app\\' . $name . '\\controller\\' . $short_class);
                $parseRoute('/' . $uri, $class_name, $action);
            }
        }
    }
    
    foreach ($module_list as $name => $info) {
        $dir = app_path() . DS . $name . DS . 'controller' . DS;
        if (!is_dir($dir)) continue;
        if (empty($info['status'])) continue;
        scan_dir($dir, function($it, $iterator) use ($dir, $name, $route, $parseRoute) {
            if ($it->isDir() || $it->getExtension() != 'php') return;
            $s_path = substr(substr($it->getPathname(), strlen($dir)), 0, -4);
            $uri = '/' . $name . '/' . str_replace(['/_', '\\_', '\\'], ['/', '/', '/'], Str::snake($s_path));
            $class_name = 'app\\' . $name . '\\controller\\' . $s_path;
            $class_name = str_replace('/', '\\', 'app\\' . $name . '\\controller\\' . $s_path);
            $parseRoute($uri, $class_name);
        });
    }
};
$_app_route_init();
Route::disableDefaultRoute();
\yi\System::write('RUNNING');