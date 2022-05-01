<?php

namespace yi\controller;

use support\Str;

abstract class IndexController extends BaseController
{
    public $needLogin = [];

    public $noNeedLogin = [];

    public $noNeedCheck = ['*'];

    public $middlewares = [
        \yi\middlewares\UserAuthCheck::class,
    ];

    public $user = null;

    public function before()
    {
        parent::before();
        $payload = (object)[
            'controller' => $this
        ];
        event('BeforeIndexController', $payload);
        $app = request()->app;
        app(\yi\Event::class)->bind('BeforeRender', 'yi\\events\\Event@onSetIndexRenderOption');
        $this->user = get_user();
        $this->config('module', request()->getModule());
        $this->config('controller', str_replace('\\', '/', request()->getController()));
        $this->config('action', request()->getAction());
        $this->config('statics', get_module_group_config('system', 'statics'));
        $this->config('admin', array_merge_deep(request()->config('common'), request()->config('admin')));
        $this->config('langVersion', ev('GetLangVersion'));
        $this->config('version', get_version());
        $this->config('lang', request()->var('locale'));
        $this->config('debug', config('app.debug'));
        $this->assign('admin', get_admin());
        $this->assign('auth', get_user());
        $this->assign('user', get_user()->getUser());
        $this->config('index', array_merge_deep(request()->config('common'), request()->config('index')));
        \yi\Widget::group('index');
        if (request()->getModule() != 'system') $this->config('moduleVersion', get_module_info(request()->getModule())['version']);
        $this->assignconfig();
        $this->loadlang();
    }

    public function after()
    {
        parent::after();
        $payload = (object)[
            'controller' => $this
        ];
        event('AfterIndexController', $payload);
    }
    
    protected function fetch(string $template = '', array $vars = [])
    {
        if (!Str::startsWith($template, '.html')) {
            $template = get_template($template);
        }
        return fetch($template, $vars);
    }
}