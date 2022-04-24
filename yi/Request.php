<?php

namespace yi;

use support\Str;

class Request extends \support\Request 
{
    protected $filters = ['htmlspecialchars'];

    protected $_config = [];
    
    protected $_vars = [];

    public function filter($filters)
    {
        $this->filters = $filters;
        return $this;
    }

    public function get($name = null, $default = null, $filters = null)
    {
        $filters = is_null($filters) ? $this->filters : $filters;
        return filter(parent::get($name, $default), $filters);
    }

    public function post($name = null, $default = null, $filters = null)
    {
        $filters = is_null($filters) ? $this->filters : $filters;
        return filter(parent::post($name, $default), $filters);
    }

    public function input($name = null, $default = null, $filters = null)
    {
        $filters = is_null($filters) ? $this->filters : $filters;
        return filter(parent::input($name, $default), $filters);
    }

    public function request($name = null, $default = null, $filters = null)
    {
        return $this->input($name, $default, $filters);
    }

    public function isPost()
    {
        return $this->method() == 'POST';
    }

    public function isGet()
    {
        return $this->method() == 'GET';
    }

    public function server($name = null)
    {
        return $name ? ($_SERVER[$name] ?? null) : $_SERVER;
    }

    public function getModule()
    {
        return $this->app;
    }
    
    public function getController()
    {
        return substr($this->controller, strlen('app\\' . $this->app . '\\controller\\'));
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getName()
    {
        return Str::studly(str_replace(['/', '\\'], ['_', '_'], $this->getModule() . '_' . $this->getController() . '_' . $this->getAction()));
    }
    
    public function config()
    {
        $args = func_get_args();
        switch (count($args)) {
            case 0 :
                return $this->_config;
            break;
            case 1 :
                return $this->_config[$args[0]] ?? [];
            break;
            case 2 : 
                return $this->_config[$args[0]][$args[1]] ?? null;
            break;
            case 3 : 
                if (!isset($this->_config[$args[0]])) $this->_config[$args[0]] = [];
                $this->_config[$args[0]][$args[1]] = $args[2];
                return $this->_config;
            break;
        }
    }
    
    public function var()
    {
        $args = func_get_args();
        if (count($args) == 1) {
            return parse_dot_row($this->_vars, $args[0]);
        } else if (count($args) == 2) {
            $key = $args[0];
            $data = $args[1];
            $keys = explode('.', $key);
            $max = count($keys) - 1;
            $result = array($keys[$max] => $data);
            for($i = $max - 1; $i >= 0; $result = array($keys[$i--] => $result));
            $this->_vars = array_replace_recursive($this->_vars, $result);
            return $this;
        }
        return $this->_vars;
    }

    public function buildToken($name = '__csrf_token__')
    {
        $token = md5(time() . Str::random(6));
        $this->session()->set($name, $token);
        return $token;
    }

    public function checkToken($token = '__csrf_token__', $data = [])
    {
        if (in_array($this->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        if (!$this->session()->has($token)) {
            return false;
        }
        
        if ($this->header('X-CSRF-TOKEN') && $this->session()->get($token) === $this->header('X-CSRF-TOKEN')) {
            $this->session()->delete($token);
            return true;
        }
        
        if (empty($data)) {
            $data = $this->post();
        }

        if (isset($data[$token]) && $this->session()->get($token) === $data[$token]) {
            $this->session()->delete($token);
            return true;
        }

        $this->session()->delete($token);
        return false;
    }
}
