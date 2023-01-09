<?php

namespace support;

//use JasonGrimes\Paginator as Pagination;
use yi\Paginator as Pagination;

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

    public static function create($total, $perPage = 15, $page = null, $link = null, $simple = false)
    {
        $page = $page ?: request()->get('page', 1);
        $param = request()->get();
        $param['page'] = '__PAGE_NUM__REPLACE__';
        $url = ($link ?: request()->url()) . '?' . http_build_query($param);
        $url = str_replace('__PAGE_NUM__REPLACE__', '(:num)', $url);
        return (new Pagination($total, $perPage, $page, $url))->setPreviousText(lang('Previous'))->setNextText(lang('Next'))->setSimple($simple);
    }

    public function render($link = null)
    {
        $paginator = $this->_paginator;
        $simple = false;
        if ($paginator instanceof \Illuminate\Pagination\Paginator) {
            $total = 0;
            $simple = true;
            $hasMore = $paginator->hasMorePages();
        } else $total = $paginator->total();
        $paginator = static::create($total, $paginator->perPage(), $paginator->currentPage(), $link, $simple);
        if ($simple) {
            $paginator->setHasMorePages($hasMore);
        }
        return $paginator;
    }
}