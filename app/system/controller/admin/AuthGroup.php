<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

/**
 * @Menu(title=Admin Roles,weigh=78000,ignore=imports|exports)
 */
class AuthGroup extends Base 
{
    protected $validate_cls = \app\system\validate\admin\AuthGroupValidate::class;
    protected $pid_name = 'pid';
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\AuthGroupLogic::instance(true);
    }
}