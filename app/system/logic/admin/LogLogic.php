<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

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
}