<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__ . DS);
define('RUNTIME_PATH', __DIR__ . DS . 'runtime' . DS);
define('APP_PATH', ROOT_PATH . 'app' . DS);
define('VIEW_PATH', ROOT_PATH . 'view' . DS);
define('PUBLIC_PATH', ROOT_PATH . 'public' . DS);
define('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);
define('EXT', '.php');
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);