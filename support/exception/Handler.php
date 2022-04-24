<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace support\exception;

use Webman\Http\Request;
use Webman\Http\Response;
use Throwable;
use Webman\Exception\ExceptionHandler;

/**
 * Class Handler
 * @package support\exception
 */
class Handler extends ExceptionHandler
{
    public $dontReport = [
        BusinessException::class,
        Exception::class,
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render(Request $request, Throwable $exception): Response
    {
        $code = $exception->getCode();
        if ($request->expectsJson()) {
            $json = ['code' => $code ? $code : 500, 'message' => $exception->getMessage(), 'data' => []];
            $this->_debug && $json['data']['traces'] = (string)$exception;
            return new Response(200, ['Content-Type' => 'application/json'],
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        $error = $this->_debug ? nl2br((string)$exception) : 'Server internal error';
        return new Response(500, [], $error);
    }

}