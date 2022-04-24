<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-13
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

/**
 * @Menu(title=User Money Logs,weigh=75000,ignore=tree|tree_list|tree_array|add|edit|all|imports|exports,ismenu=1)
 */
class UserMoneyLog extends Base 
{
    protected $validate_cls = \app\system\validate\admin\UserMoneyLogValidate::class;
    protected $has_tree = false;
    
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\UserMoneyLogLogic::instance(true);
    }
}