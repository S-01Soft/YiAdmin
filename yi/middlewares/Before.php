<?php

namespace yi\middlewares;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class Before implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        app(\yi\Event::class)->clear();
        return $next($request);
    }
}