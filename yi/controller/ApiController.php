<?php

namespace yi\controller;

abstract class ApiController extends BaseController
{
    public $needLogin = [];

    public $noNeedLogin = [];

    public $noNeedCheck = ['*'];

    public $corsAllowHeaders = '*';

    public $middlewares = [
        \yi\middlewares\CORS::class,
        \yi\middlewares\ApiAuthCheck::class,
    ];

    public $user = null;

    public function before()
    {
        parent::before();
        $payload = (object)[
            'controller' => $this
        ];
        event('BeforeApiController', $payload);
    }

    public function after()
    {
        parent::after();
        $payload = (object)[
            'controller' => $this
        ];
        event('AfterApiController', $payload);
    }

    protected function error($message = '', $code = 10000, $data = [])
    {
        return error($message, $code, $data, 'json');
    }
}