<?php

namespace app\%name%;

use yi\Menu;

class Plugin
{
    public static function install() 
    {
        Menu::install('%name%', 'admin', 'admin');
        Menu::install('%name%', 'user', 'index');
        Menu::install('%name%', 'user', 'api');
    }

    public static function uninstall()
    {
        Menu::uninstall('%name%', 'admin');
        Menu::uninstall('%name%', 'user');
    }

    public static function enable()
    {
        Menu::enable('%name%', 'admin');
        Menu::enable('%name%', 'user');
    }

    public static function disable()
    {
        Menu::disable('%name%', 'admin');
        Menu::disable('%name%', 'user');
    }
}