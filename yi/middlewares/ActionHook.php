<?php

namespace yi\middlewares;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\exception\Exception;
use support\Container;

class ActionHook implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        $controller = $request->controller;
        if (in_array($request->action, ['after', 'before'])) return new Response(404, [], \file_get_contents(public_path() . '/404.html'));
        $class = app($controller);
        if (method_exists($class, 'before')) {
            $before_response = call_user_func([$class, 'before']);
            if ($before_response instanceof Response) {
                return $before_response;
            }
        }
        if (method_exists($class, 'after')) {
            $after_response = call_user_func([$class, 'after']);
            if ($after_response instanceof Response) {
                return $after_response;
            }
        }
        return $next($request);
    }
}