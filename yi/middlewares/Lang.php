<?php

namespace yi\middlewares;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\exception\Exception;

class Lang implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        $lang = $request->cookie('lang') ?: ($request->header('lang') ?: ($request->get('lang') ?: config('translation.locale')));
        $request->var('lang', new \yi\Lang($lang));
        $request->var('locale', $lang);
        locale($lang);
        return $next($request);
    }
}