<?php

namespace yi\subscribe;

use yi\EventLib;

class Event
{
    protected $classMaps = [];

    public function onBindEvent($payload)
    {
        if (is_installed()) {
            try {
                $e = app(\yi\EventLib::class);
                $events = $e->getListeners();
                $e->bind($events);
            } catch(\Throwable $e) {
                logs()->error('Bind event error: ' . $e->getMessage());
            }
        }
    }

    public function onAddNamespace($payload)
    {
        list($namespace, $path) = $payload->params;
        if (isset($this->classMaps[$namespace])) {
            if (!in_array($path, $this->classMaps[$namespace])) $this->classMaps[$namespace][] = $path;
        }
        else $this->classMaps[$namespace] = [$path];
    }

    public function onCustomNamespaces($payload)
    {
        $payload->result = $this->classMaps;
    }

    public function onSyncData($payload)
    {
        if (!is_installed()) return;
        \Webman\Channel\Client::connect('127.0.0.1', env('CHANNEL_PORT'));
        $args = $payload->params;
        if (count($args) == 2) $args[] = [];
        if (count($args) == 3) $args[] = [];
        \Webman\Channel\Client::publish('SyncData', $args);
    }
}