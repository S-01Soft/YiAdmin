<?php

namespace yi;

class Annotation
{
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    public function parseClassComment($cls)
    {
        return $this->parseComment($cls->getDocComment());
    }

    public function parseMethodComment($method)
    {
        $comment = $method->getDocComment();
        return $this->parseComment($comment);
    }

    public function parseComment($content)
    {
        $result = [];
        foreach ($this->config as $field => $option) {
            if (is_numeric($field)) {
                $field = $option;
            }
            $result[$field] = $this->parse($content, $field);
        }
        return $result;
    }

    protected function parse($content, $field)
    {
        preg_match_all("/(?<=@" . $field . "\().+(?=\))/", $content, $matches);
        $result = [];
        if (!empty($matches[0])) {
            foreach ($matches[0] as $i => $match) {
                $arr = explode(',', $match);
                $item = [];
                foreach ($arr as $vo) {
                    $val = explode('=', $vo, 2);
                    $default = 'string';
                    $k = $this->clearUseLessChar($val[0]);
                    $v = count($val) == 1 ? 1 : $this->clearUseLessChar($val[1]);
                    $type = empty($this->config[$field][$k]) ? $default : $this->config[$field][$k];
                    switch ($type) {
                        case 'int' :
                            $v = (int)$v;
                        break;
                        case 'boolean' :
                            $v = (bool)$v;
                        break;
                        case 'array' :
                            $v = (array)json_decode($v);
                        break;
                    }
                    $item[$k] = $v;
                }
                $result[] = $item;
            }
        }
        return $result;
    }

    private function clearUseLessChar($str)
    {
        $str = trim($str);
        $str = str_replace("\"", "", $str);
        $str = str_replace("\'", "", $str);
        return $str;
    }
}