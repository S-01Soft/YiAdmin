<?php

namespace yi;

use GuzzleHttp\Client;
use support\exception\Exception;

class Http
{

    public static function client()
    {
        return new Client();
    }

    public static function request($method, $url, $options = [], $contentType = 'json')
    {
        try {
            $options = array_merge([
                'timeout' => 10
            ], $options);
            $response = static::client()->request($method, $url, $options);
            $code = $response->getStatusCode();
            if ($code == 200) {
                switch (strtolower($contentType)) {
                    case 'json':
                        $content = $response->getBody()->getContents();
                        $json = (array)json_decode($content, true);
                        return $json ?: $content;
                    break;
                    default:
                        return $response;
                    break;
                }
            }
            throw new Exception($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            throw new Exception("请求的服务器异常：" . $e->getMessage());
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception("网络异常：" . $e->getMessage());
        }
    }

    public static function get($url, $params = [], $options = [], $contentType = 'json')
    {
        $options['query'] = $params;
        return static::request('GET', $url, $options, $contentType);
    }

    public static function post($url, $params = [], $type = 'json', $options = [], $contentType = 'json')
    {
        switch (strtolower($type)) {
            case 'json':
                $options['json'] = $params;
            break;
            case 'form':
                $options['form_params'] = $params;
            break;
            case 'multipart':
                $options['multipart'] = $params;
            break;
        }
        return static::request('POST', $url, $options, $contentType);
    }
}