<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

/**
 * @Menu(title=Events Listeners,weigh=10000,ignore=tree|tree_list|tree_array|select|all|add|edit|imports|exports,ismenu=0)
 */
class EventApps extends Base 
{
    protected $validate_cls = \app\system\validate\admin\EventAppsValidate::class;
    protected $has_tree = false;
    
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\EventAppsLogic::instance(true);
    }
}