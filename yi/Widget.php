<?php

namespace yi;

use support\View;
use support\Str;
use support\Collection;

class Widget
{
    static $_instance;

    public static function __callStatic($name, $args)
    {
        $_instance = static::instance();
        return $_instance->$name(...$args);
    }
    
    public static function instance()
    {
        if (!static::$_instance) static::$_instance = new WidgetView;
        return static::$_instance;
    }
    public static function newInstance()
    {
        static::$_instance = null;
        return static::instance();
    }
}

class WidgetView
{
    protected $events = [];
    protected $option = [];
    protected $widget = [];
    protected $widgetOptions = [];
    protected $_instance;
    protected $_group = 'admin';
    protected $_callback = [];
    protected $vars = [];
    protected $view = null;

    public function init()
    {
        $module = request()->getModule();
        $controller = snake_controller(request()->getController(), DS);
        $action = request()->getAction();
        $this->option = array_merge($this->option, [
            'module' => $module,
            'controller' => $controller,
            'action' => $action
        ]);
        $this->view = config('view.handler');
        return $this;
    }

    public function render($name, $param = [], $template = '')
    {
        $array = explode(',', $name);
        foreach ($array as $v) {
            $this->render_one($v, $param, $template);
        }
        $events = $this->events[$this->_group] ?? [];
        $_callback = $this->_callback[$this->getGroup()] ?? null;
        if ($_callback instanceof \Closure) $_callback();
        
        foreach ($events as $event => $data) {
            if ($event == $name) {
                $data = (new collection($data))->order('weigh', 'desc')->toArray();
                foreach ($data as $item) {
                    if (!empty($item['app']) && $item['app'] != $this->option['module']) continue;
                    if (!empty($item['controller']) && $item['controller'] != $this->option['controller']) continue;
                    if (!empty($item['action']) && $item['action'] != $this->option['action']) continue;
                    if ($item['template'] instanceof \Closure) $item['template']($item, $param, $template);
                    else {
                        $widgets = [
                            'vars' => $item['param'],
                            'args' => $param
                        ];
                        echo $this->view::fetch($item['template'], $widgets);
                    }
                }
            }
        }
    }

    public function render_one($name, $param, $template)
    {
        $this->option['template'] = $template;
        $option = $this->getWidgetOption();
        $array = $option[$this->option['action']] ?? [];
        $view_base = str_replace('.', '/', $this->option['controller']) . '/';

        foreach ($array as $k => $v) {
            if ($name != $k) continue;
            if ($v instanceof \Closure) {
                $v();
                continue;
            }
            $files = is_array($v) ? $v : explode(',', $v);
            foreach ($files as $file) {
                $path = $view_base . $file;
                echo $this->view::fetch($path, $param);
            }
        }
    }

    public function add($event, $template = '', $weigh = 10000, $param = [], $app = null, $controller = null, $action = null)
    {
        if (is_array($event)) {
            foreach ($event as $args) {
                $this->add(...$args);
            }
        } else {
            $data = [
                'action' => $action,
                'controller' => $controller,
                'app' => $app,
                'param' => $param,
                'template' => $template,
                'weigh' => $weigh,
            ];
            if (isset($this->events[$this->_group][$event])) $this->events[$this->_group][$event][] = $data;
            else $this->events[$this->_group][$event] = [$data];
        }
    }

    public function config()
    {
        $args = func_get_args();
        if (count($args) == 1) {
            if (is_array($args[0])) {
                $this->option = array_merge($this->option, $args[0]);
                return $this;
            } elseif (is_string($args[0])) {
                return $this->option[$args[0]];
            }
        } elseif (count($args) == 2) {
            $this->option[$args[0]] = $args[1];
            return $this;
        }
        return $this;
    }

    public function var()
    {
        $args = func_get_args();
        if (count($args) == 1) {
            return $this->vars[$this->getGroup()][$args[0]];
        } else if (count($args) == 2) {
            if (empty($this->vars[$this->getGroup()])) $this->vars[$this->getGroup()] = [];
            if (empty($this->vars[$this->getGroup()][$args[0]])) $this->vars[$this->getGroup()] = [$args[0] => $args[1]];
            $this->vars[$this->getGroup()][$args[0]] = $args[1];
            return $this;
        }
    }

    protected function getWidgetOption()
    {
        if (empty($this->option['template'])) {
            $file = str_replace(['/', '\\'], [DS, DS], app_path() . DS . request()->getModule() . DS . 'view' . DS . snake_controller(request()->getController(), DS) . '/widget.php');
        } else {
            $file = config('view.options.view_path') . $this->option['template'] . DS . 'widget.php';
        }
        $name = md5($file);
        if (!empty($this->widgetOptions[$name])) return $this->widgetOptions[$name];
        $config = file_exists($file) ? include $file : [];

        $this->widgetOptions[$name] = $config;
        return $config;
    }

    public function group($group)
    {
        return $this->setGroup($group);
    }

    public function setGroup($group)
    {
        $this->_group = $group;
        return $this;
    }

    public function getGroup()
    {
        return $this->_group;
    }

    public function beforeRender($cb)
    {
        $this->_callback[$this->getGroup()] = $cb;
        return $this;
    }
}