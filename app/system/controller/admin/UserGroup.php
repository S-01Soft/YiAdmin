<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

/**
 * @Menu(title=User Roles,weigh=76000,ismenu=1,ignore=imports|exports)
 */
class UserGroup extends Base 
{
    protected $validate_cls = \app\system\validate\admin\UserGroupValidate::class;
    protected $pid_name='pid';
    
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\UserGroupLogic::instance(true);
    }
}