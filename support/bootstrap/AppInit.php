<?php

namespace support\bootstrap;

use Webman\Bootstrap;
use support\Container;

class AppInit implements Bootstrap
{
    public static function start($worker)
    {
        \yi\Validate::maker(function($validate) {
            $validate->extend('captcha', function ($value) {
                return session('captcha') == strtolower($value);
            }, ':attribute错误!');
        });
        $module = Container::get(\yi\Module::class);
        $module->refresh();
    }
}