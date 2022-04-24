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

namespace yi;

use think\Template;
/**
 * Class View
 * @package yi\view
 */
class View implements \Webman\View
{
    /**
     * @var array
     */
    protected static $_vars = [];

    /**
     * @param $name
     * @param null $value
     */
    public static function assign($name, $value = null)
    {
        static::$_vars = \array_merge(static::$_vars, \is_array($name) ? $name : [$name => $value]);
    }

    public static function handler()
    {
        $default_options = [
            'cache_path' => \runtime_path() . '/views/',
            'view_suffix' => config('view.view_suffix', 'html')
        ];
        $options = $default_options + \config('view.options', []);
        $payload = (object) [
            'options' => $options
        ];
        event('BeforeRender', $payload);
        return new Template($payload->options);
    }

    /**
     * @param $template
     * @param $vars
     * @param string $app
     * @return mixed
     */
    public static function render($template, $vars = [], $app = null)
    {
        $view = static::handler();
        \ob_start();
        $vars = \array_merge(static::$_vars, $vars);
        $view->fetch($template, $vars);
        $content = \ob_get_clean();
        static::$_vars = [];
        return $content;
    }

    public static function fetch($template, $vars = [], $app = null)
    {
        $view = static::handler();
        \ob_start();
        $vars = \array_merge(static::$_vars, $vars);
        $view->fetch($template, $vars);
        $content = \ob_get_clean();
        return $content;
    }

    /**
     * @param $template
     * @param $vars
     * @param string $app
     * @return mixed
     */
    public static function display($content, $vars = [], $app = null)
    {
        $view = static::handler();
        \ob_start();
        $vars = \array_merge(static::$_vars, $vars);
        $view->display($content, $vars);
        $content = \ob_get_clean();
        return $content;
    }
}
