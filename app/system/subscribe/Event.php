<?php

namespace app\system\subscribe;

use yi\Annotation;
use app\system\model\admin\LogModel;

class Event 
{
    protected $langVersion = null;

    public function onGetLangVersion($payload)
    {
        $version = $this->langVersion ?: cache('SYSTEM_LANG_VERSION');
        if (is_null($version)) {
            event('RefreshLangVersion');
            $version = $this->langVersion;
        }
        $payload->result = $version;
    }

    public function onRefreshLangVersion($payload)
    {
        $version = time();
        cache('SYSTEM_LANG_VERSION', $version);
        $this->langVersion = $version;
    }

    public function onAfterIndexController($payload)
    {
        $log = $payload->controller->log;
        if (!empty($log['record'])) {
            $data = $this->getData($log['title'] ?? null, $log['content'] ?? null, 'index');
            $data['user_id'] = get_user()->id ?: 0;
            $data['username'] = get_user()->username ?: '';
            LogModel::create($data);
        }
    }

    public function onAfterAdminController($payload)
    {
        $log = $payload->controller->log;
        if (!empty($log['record'])) {
            $data = $this->getData($log['title'] ?? null, $log['content'] ?? null, 'admin');
            $data['user_id'] = get_admin()->id ?: 0;
            $data['username'] = get_admin()->username ?: '';
            LogModel::create($data);
        }
    }

    public function onAfterApiController($payload)
    {
        $log = $payload->controller->log;
        if (!empty($log['record'])) {
            $data = $this->getData($log['title'] ?? null, $log['content'] ?? null, 'api');
            $data['user_id'] = get_user()->id ?: 0;
            $data['username'] = get_user()->username ?: '';
            LogModel::create($data);
        }
    }

    public function getData($title, $content, $type)
    {
        if (!$content) {
            $content = request()->post(null, '', 'trim,strip_tags,htmlspecialchars');
            $content = $this->getWaterContent($content);
        }
        if (!$title) {
            $class = request()->controller;
            if (class_exists($class)) {
                $parser = new Annotation(['Menu']);
                $refClass = new \ReflectionClass($class);
                $action = request()->action;
                if ($refClass->hasMethod($action)) {
                    $method = $refClass->getMethod($action);
                    $classParams = $parser->parseClassComment($refClass);
                    $methodParams = $parser->parseMethodComment($method);
                    if (!empty($classParams['Menu'][0]['title'])) $title = $classParams['Menu'][0]['title'];
                    if (!empty($methodParams['Menu'][0]['title'])) $title .= '/' . $methodParams['Menu'][0]['title'];
                }
            }
        }
        
        $data = [
            'title' => $title,
            'content' => !is_scalar($content) ? json_encode($content, JSON_UNESCAPED_UNICODE) : $content,
            'url' => request()->uri(),
            'ip' => get_ip(),
            'ajax' => request()->isAjax() ? 1 : 0,
            'method' => request()->method(),
            'referer' => request()->header('REFERER') ?: '',
            'useragent' => http_user_agent() ?: '',
            'type' => $type,
            'app' => request()->getModule()
        ];
        return $data;
    }

    public function getWaterContent($content)
    {
        if (!is_array($content)) {
            return $content;
        }
        foreach ($content as $k => &$item) {
            if (preg_match("/(password|salt|token)/i", $k)) {
                $item = "***";
            } else {
                if (is_array($item)) {
                    $item = $this->getWaterContent($item);
                }
            }
        }
        return $content;
    }
}