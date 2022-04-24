<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\logic\admin;

use yi\Auth;

class AuthRuleLogic extends Logic
{
    protected function initialize()
    {
        $this->static = \app\system\model\admin\AuthRuleModel::class;
        parent::initialize();
    }

    public function paginateView($c)
    {
        $c->hidden = ['add_btn', 'edit_btn', 'delete_btn'];
    }

    protected function beforePaginate($query)
    {
        $query->with(['pid_c']);
    }
    
    protected function afterPaginate($result)
    {
        foreach($result as $row) {
            $row->visible(['id','title','ismenu','created_at','weigh','status','app','app_type','pid_c']);
            if($row->pid_c) $row->pid_c->visible(['id','name']);
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
            $row->visible(['id','title','ismenu','created_at','weigh','status','app','app_type','pid_c']);
            if($row->pid_c) $row->pid_c->visible(['id','name']);
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
            $row->visible(['id','title','ismenu','created_at','weigh','status','app','app_type','pid_c']);
            if($row->pid_c) $row->pid_c->visible(['id','name']);
        }
        return $result;
    }

    protected function beforeQueryTree($query)
    {
        $auth = get_admin();
        $where = [];
        $type = request()->get('type');
        if (!empty($type)) $query->where('type', 1)->where('ismenu', 1);
        if ($auth->id != 1) {
            $rules = $this->getRules($auth->id);
            $query->whereIn('id', $rules);
        }
        $query->orderByRaw('weigh DESC,id ASC');
    }

    protected function beforeTreeList($payload)
    {
        $app = request()->get('app');
        if (!empty($app)) {
            $rule = $this->static::where('name', $app)->first();
            if ($rule) $payload->id = $rule->id;
            else $payload->id = -1;
        } else $payload->id = 0;        
    }
    
    public function getRules($id = null)
    {
        $id = $id ?: get_admin()->id;
        $groups = AuthGroupLogic::instance()->getGroups($id);
        $ids = [];
        foreach ($groups as $v) {
            $ids = array_merge($ids, explode(',', $v['rules']));
        }
        return array_unique($ids);
    }
}