<?php

namespace yi;

use support\Translation;
use support\Str;

class Lang 
{
    protected $locale = 'zh-cn';

    protected $translator = null;

    protected $values = [];

    public function __construct($locale = 'zh-cn')
    {
        $this->translator = Translation::instance();
        $this->locale = $locale;
    }

    public function load($files)
    {
        if (is_string($files)) $files = explode(',', $files);
        foreach ($files as $file) {
            if (file_exists($file)) {
                $this->translator->addResource('phpfile', $file, $this->locale);
                $v = include $file;
                $this->values = array_merge($this->values, $v ?: []);
            }
        }
        return $this;
    }

    public function loadDirs($dirs)
    {
        if (is_string($dirs)) $dirs = explode(',', $dirs);
        $files = [];
        foreach ($dirs as $dir) {
            $files[] = $dir . (Str::endsWith($dir, DS) ? '' : DS) . $this->locale . '.php';
        }
        return $this->load($files);
    }

    public function get($key = '')
    {
        $data = [];
        foreach ($this->values as $k => $v) {
            $data[strtolower($k)] = $v;
        }
        if (empty($key)) return $data;
        return $data[$key] ?? '';
    }

    public function has($key)
    {
        return !empty($this->get()[$key]);
    }

    public function getLocale()
    {
        return $this->locale;
    }
}