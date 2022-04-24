<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

/**
 * @Menu(title=Admins,weigh=79000,ignore=tree|tree_list|tree_array|imports|exports,ismenu=1)
 */
class Admin extends Base 
{
    protected $validate_cls = \app\system\validate\admin\AdminValidate::class;
    protected $has_tree = false;
    
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\AdminLogic::instance(true);
    }
}