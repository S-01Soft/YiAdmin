<?php

namespace support;

use JasonGrimes\Paginator as Pagination;

class Paginator
{
    protected $_paginator = null;

    public static function instance($paginator)
    {
        return new static($paginator);
    }

    public function __construct($paginator)
    {
        $this->_paginator = $paginator;
    }

    public static function create($total, $perPage = 15, $page = null, $link = null)
    {
        $page = $page ?: request()->get('page', 1);
        $param = request()->get();
        $param['page'] = '__PAGE_NUM__REPLACE__';
        $url = ($link ?: request()->url()) . '?' . http_build_query($param);
        $url = str_replace('__PAGE_NUM__REPLACE__', '(:num)', $url);
        return (new Pagination($total, $perPage, $page, $url))->setPreviousText(lang('Previous'))->setNextText(lang('Next'));
    }

    public function render($link = null)
    {
        $paginator = $this->_paginator;
        return static::create($paginator->total(), $paginator->perPage(), $paginator->currentPage(), $link);
    }
}