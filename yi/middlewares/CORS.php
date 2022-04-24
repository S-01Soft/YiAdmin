<?php

namespace yi\middlewares;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class CORS implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        $response = $request->method() == 'OPTIONS' ? response('') : $next($request);
        
        $class_name = $request->controller;
        $class = app($class_name);
        $corsAllowHeaders = $class->corsAllowHeaders;
        $response->withHeaders([
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => $corsAllowHeaders,
            'Access-Control-Allow-Methods' => '*',
            'Access-Control-Allow-Headers' => '*',
        ]);

        return $response;
    }
}