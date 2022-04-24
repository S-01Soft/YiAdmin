<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-13
 * @LastEditors: 01Soft
 */

namespace app\system\logic\admin;

class UserScoreLogLogic extends Logic
{
    protected function initialize()
    {
        $this->static = \app\system\model\admin\UserScoreLogModel::class;
        parent::initialize();
    }
    
    public function paginateView($c)
    {
        $c->hidden = ['add_btn', 'slot_edit_btn', 'import_btn'];
    } 
    protected function beforePaginate($query)
    {
        $query->with(['user']);
    }  
}