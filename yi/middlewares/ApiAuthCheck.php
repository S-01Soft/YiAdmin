<?php

namespace yi\middlewares;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use yi\User;

class ApiAuthCheck implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        $class_name = $request->controller;
        $class = app($class_name);
        $needLogin = $class->needLogin;
        $noNeedLogin = $class->noNeedLogin;
        $noNeedCheck = $class->noNeedCheck;

        $action = $request->action;
        $token = $request->header('token') ?: $request->input('token');
        $user = \yi\User::instance();
        $user->init($token);
        $request->user = $user;
        $payload = (object) [
            'controller' => $class,
            'class_name' => $class_name,
            'response' => null
        ];
        event('BeforeApiAuthCheck', $payload);
        if (is_a($payload->response, Response::class)) return $payload->response;
        if (in_array($action, $noNeedLogin) || in_array('*', $noNeedLogin)) return $next($request);
        if (in_array($action, $needLogin) || in_array('*', $needLogin)) {
            if (!$user->isLogin()) return error('您未登录', 9999, [], 'json'); 
        }

        if (in_array($action, $noNeedCheck) || in_array('*', $noNeedCheck)) return $next($request);
        if (!in_array($action, $noNeedCheck) || in_array('*', $noNeedCheck)) {
            $rule = '/' . $request->getModule() . '/' . snake_controller($request->getController()) . '/' . $request->getAction();
            $rule = str_replace('\\', '/', $rule);
            if ($user->id !== 1 && !$user->check($rule, $user->id, 1, 'url')) return error('您没有该操作权限', 401, [], 'json');
        }
        return $next($request);
    }
}