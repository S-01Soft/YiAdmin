<?php

namespace yi\controller;

use support\Response;
use yi\Widget;

abstract class BaseController 
{
    public $middlewares = [];

    public $noNeedLogin = [];
    public $needLogin = [];
    public $needCheck = [];

    public $config_key = '__CONTROLLER_ASSIGN_CONFIG_';

    protected $_config = [];

    public $error_tmpl = null;
    
    protected $request = null;

    protected $view = null;
    
    const CSRF_TOKEN_ERR = 9001;

    public $log = [
        'title' => null, 'content' => null, 'record' => true
    ];

    public function before()
    {
        $this->request = request();
        $this->view = config('view.handler');
        $payload = (object)[
            'controller' => $this
        ];
        event('BindEvent', $payload);
        Widget::newInstance();
        event('HttpRun', $payload);
    }

    public function after()
    {
        $payload = (object)[
            'controller' => $this
        ];
        event('HttpEnd', $payload);
    }
    
    protected function success($data = [], $message = '', $callback = null)
    {
        return success($data, $message, $callback);
    }

    protected function error($message = '', $code = 10000, $data = null)
    {
        $error_tmpl = $this->error_tmpl ?: config('app.dispatch_error_tmpl');
        return error($message, $code, $data, null, $error_tmpl);
    }
    
    protected function loadlang()
    {
        $langDirs = [];
        foreach (get_full_module_list() as $info) {
            $langDirs[] = app_path() . DS . $info['name'] . DS . 'lang' . DS;
        }
        $modulePath = app_path() . DS . request()->getModule() . DS;
        request()->var('lang')->loadDirs(array_merge($langDirs, [
            $modulePath . 'lang' . DS, 
            $modulePath . 'lang' . DS . snake_controller(request()->getController(), DS) . DS
        ]));
    }

    protected function assignconfig()
    {
        $this->assign('config', request()->var($this->config_key));
    }

    protected function assign($name, $value) 
    {
        $this->view::assign($name, $value);
        return $this;
    }

    protected function token()
    {
        if (request()->checkToken() === false) return $this->error(lang('The token is invalid, please try again'), static::CSRF_TOKEN_ERR, ['token' => request()->buildToken()]);
    }
    
    protected function config()
    {
        $args = func_get_args();
        switch (count($args)) {
            case 0 :
                return request()->var($this->config_key);
            break;
            case 1 :
                return request()->var($this->config_key . '.' . $args[0]);
            break;
            case 2 : 
                request()->var($this->config_key . '.' . $args[0], $args[1]);
                return $this->config();
            break;
        }
    }
}