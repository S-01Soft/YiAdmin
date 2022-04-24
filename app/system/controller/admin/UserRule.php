<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

/**
 * @Menu(off,title=User Rules,weigh=10000,ismenu=0)
 */
class UserRule extends Base 
{
    protected $validate_cls = \app\system\validate\admin\UserRuleValidate::class;
    protected $pid_name='pid';
    
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\UserRuleLogic::instance(true);
    }
}