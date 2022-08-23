<?php

namespace app\system\logic\admin;

use yi\Annotation;

class LogLogic extends Logic
{
    protected static $title;

    protected static $content;

    protected function initialize()
    {
        $this->static = \app\system\model\admin\LogModel::class;
        parent::initialize();
    }

    public function paginateView($c) 
    {
        $c->hidden = ['add_btn', 'slot_edit_btn'];
    }
}