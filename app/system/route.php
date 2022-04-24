<?php

use Webman\Route;

Route::any('/system', [\app\system\controller\admin\Index::class, 'index'])->middleware([
    \yi\middlewares\AdminAuthCheck::class,
    \yi\middlewares\ActionHook::class,
]);
Route::any('/captcha.html', function($request) {
    return captcha();
});

Route::any('/_lang', [\app\system\controller\admin\Index::class, 'lang']);
