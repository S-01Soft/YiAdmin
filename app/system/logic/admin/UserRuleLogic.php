<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\logic\admin;

class UserRuleLogic extends Logic
{
    protected function initialize()
    {
        $this->static = \app\system\model\admin\UserRuleModel::class;
        parent::initialize();
    }

    public function paginateView($c)
    {
        $c->hidden = ['add_btn', 'delete_btn'];
    }

    protected function beforePaginate($query)
    {
        $query->with(['pid_c']);
    }
    protected function afterPaginate($result)
    {
        foreach($result as $row) {
            $row->visible(['id','type','pid','name','title','icon','condition','remark','ismenu','created_at','updated_at','weigh','status','app','parent_rule','app_type','pid_c']);
            if($row->pid_c) $row->pid_c->visible(['id','key','value']);
        }
        return $result;
    }
    protected function beforeSelect($query)
    {
        $query->with(['pid_c']);
    }
    protected function afterSelect($result)
    {
        foreach($result as $row) {
            $row->visible(['id','type','pid','name','title','icon','condition','remark','ismenu','created_at','updated_at','weigh','status','app','parent_rule','app_type','pid_c']);
            if($row->pid_c) $row->pid_c->visible(['id','key','value']);
        }
        return $result;
    }
    protected function beforeAll($query)
    {
        $query->with(['pid_c']);
    }
    protected function afterAll($result)
    {
        foreach($result as $row) {
            $row->visible(['id','type','pid','name','title','icon','condition','remark','ismenu','created_at','updated_at','weigh','status','app','parent_rule','app_type','pid_c']);
            if($row->pid_c) $row->pid_c->visible(['id','key','value']);
        }
        return $result;
    }
}