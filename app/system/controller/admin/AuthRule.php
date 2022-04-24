<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

/**
 * @Menu(title=Menu,weigh=50000,ismenu=1,ignore=add|edit|select|imports|exports)
 */
class AuthRule extends Base 
{
    protected $validate_cls = \app\system\validate\admin\AuthRuleValidate::class;
    protected $pid_name='pid';
    protected $field_name = 'title';
    
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\AuthRuleLogic::instance(true);
    }
}