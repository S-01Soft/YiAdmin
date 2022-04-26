<?php

namespace yi\middlewares;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use yi\User;
use yi\Tree;

class AdminAuthCheck implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        $class_name = $request->controller;
        $class = app($class_name);
        $needLogin = $class->needLogin;
        $noNeedLogin = $class->noNeedLogin;
        $noNeedCheck = $class->noNeedCheck;

        $action = $request->action;
        $admin = \yi\Admin::instance();
        $request->admin = $admin;
        $payload = (object) [
            'controller' => $class,
            'class_name' => $class_name,
            'response' => null
        ];
        event('BeforeAdminAuthCheck', $payload);
        if (is_a($payload->response, Response::class)) return $payload->response;
        if (in_array($action, $noNeedLogin) || in_array('*', $noNeedLogin)) return $next($request);
        if (in_array($action, $needLogin) || in_array('*', $needLogin)) {
            if (!$admin->isLogin()) {
                if (request()->isAjax() || request()->expectsJson()) return error('您未登录', 9999);
                else return redirect('/system/admin/index/login?referer=' . urlencode(request()->url()));
            }
            if ($admin->status == 0) {
                return error(lang('The account is disabled'));
            }
        }

        if (in_array($action, $noNeedCheck) || in_array('*', $noNeedCheck)) return $next($request);
        if (!in_array($action, $noNeedCheck) || in_array('*', $noNeedCheck)) {
            $rule = '/' . $request->getModule() . '/' . snake_controller($request->getController()) . '/' . $request->getAction();
            $rule = str_replace('\\', '/', $rule);
            if ($admin->id !== 1 && !$admin->check($rule, $admin->id)) {
                return error('您没有该操作权限 [' . $rule . ']', 401, '', null, $class->error_tmpl);
            }
        }
        return $next($request);
    }
}