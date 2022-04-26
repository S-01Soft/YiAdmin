<?php

namespace yi\middlewares;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use yi\User;
use yi\Tree;

class UserAuthCheck implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        $class_name = $request->controller;
        $class = app($class_name);
        $needLogin = $class->needLogin;
        $noNeedLogin = $class->noNeedLogin;
        $noNeedCheck = $class->noNeedCheck;

        $action = $request->action;
        $user = \yi\User::instance();
        $request->user = $user;
        $request->admin = \yi\Admin::instance();
        $payload = (object) [
            'controller' => $class,
            'class_name' => $class_name,
            'response' => null
        ];
        event('BeforeUserAuthCheck', $payload);
        if (is_a($payload->response, Response::class)) return $payload->response;
        if (in_array($action, $noNeedLogin) || in_array('*', $noNeedLogin)) return $next($request);
        if (in_array($action, $needLogin) || in_array('*', $needLogin)) {
            if (!$user->isLogin()) {
                return error('您未登录', 9999, '', null, $class->error_tmpl);
            }
        }

        if (in_array($action, $noNeedCheck) || in_array('*', $noNeedCheck)) return $next($request);
        if (!in_array($action, $noNeedCheck) || in_array('*', $noNeedCheck)) {
            $rule = '/' . $request->getModule() . '/' . snake_controller($request->getController()) . '/' . $request->getAction();
            $rule = str_replace('\\', '/', $rule);
            if ($user->id !== 1 && !$user->check($rule, $user->id, '', 1)) return error('您没有该操作权限', 401, '', null, $class->error_tmpl);
        }
        return $next($request);
    }
}