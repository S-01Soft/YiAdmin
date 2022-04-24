<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

/**
 * @Menu(title=Logs Manager,weigh=7000,ignore=all|tree|tree_list|tree_array|select|toggle|add|edit|imports|exports,ismenu=1)
 */
class Log extends Base 
{
    protected $validate_cls = \app\system\validate\admin\LogValidate::class;
    protected $has_tree = false;
    
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\LogLogic::instance();
    }
}