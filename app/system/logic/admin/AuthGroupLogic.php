<?php

namespace app\system\logic\admin;

use support\exception\Exception;
use yi\Auth;
use yi\Tree;
use yi\View;

class AuthGroupLogic extends Logic
{

    protected $groups;
    protected $tree;

    protected function initialize()
    {
        $this->static = \app\system\model\admin\AuthGroupModel::class;
        parent::initialize();
        $this->auth = get_admin();
    }

    public function paginateView($c)
    {
        if ($this->auth->id != 1) View::assign('groups', $this->getGroups());
    }

    protected function beforePaginate($query)
    {
        $query->with(['pid']);
        if ($this->auth->id != 1 && request()->get('group_id')) {
            $query->where('pid', request()->get('group_id'));
        }
    }

    protected function beforePostAdd($form)
    {
        $form['rules'] = is_array($form['rules']) ? implode(',', $form['rules']) : $form['rules'];
        $this->checkIsChild($form['pid']);
        $this->checkRules($form['rules']);
        return $form;
    }

    protected function afterGetEdit($data)
    {
        $data['rules'] = array_map('intval', explode(',', $data['rules']));
        return $data;
    }

    protected function beforePostEdit($form, $model)
    {
        if ($form['pid'] == $form['id']) throw new Exception(lang('The parent cannot be itself'));
        $tree = $this->getGroupTree();
        $children = $tree->getChildrenIds($form['id']);
        
        if (in_array($form['pid'], $children)) throw new Exception(lang('A child cannot be changed to a parent'));
        $this->checkIsChild($form['pid']);
        $this->checkRules($form['rules']);
        $form['rules'] = implode(',', $form['rules']);
        return $form;
    }
    
    public function tree_list($field = 'name', $pid_name = 'parent_id', $id_name = 'id', $id = 0)
    {
        $id = request()->get('group_id');
        $tree = $this->tree($pid_name, $id_name, $id);
        return $tree->getTreeList($tree->getTreeArray($id), $field);
    }

    protected function checkIsChild($id)
    {
        $auth = get_admin();
        if ($auth->id === 1) return true;
        $groups = $this->getGroups();
        $tree = $this->getGroupTree();
        foreach ($groups as $group) {
            $children = $tree->getChildrenIds($group['group_id'], true);
            if (in_array($id, $children)) return true;
        }
        throw new Exception(lang('You can only add or modify your subordinates'));
    }

    /**
     * 判断是否超出自身权限
     */
    protected function checkRules($rules)
    {
        $auth = get_admin();
        if ($auth->id === 1) return true;
        if (is_string($rules)) $rules = explode(',', $rules);
        $groups = $this->getGroups();
        $my_rules = [];
        foreach ($groups as $group) {
            $my_rules = array_merge($my_rules, explode(',', $group['rules']));
        }
        foreach ($rules as $rule) {
            if (!empty($rule) && !in_array($rule, $my_rules)) throw new Exception(lang('It is beyond your permission'));
        }
    }

    public function getGroups($uid = null)
    {
        $auth = $this->auth;
        $uid = $uid ?: $auth->id;
        if (!isset($this->groups[$uid])) {
            $this->groups[$uid] = $auth->getGroups($uid);
        }
        return $this->groups[$uid];
    }

    public function getGroupsIds($uid = null)
    {
        $data = [];
        $groups = $this->getGroups($uid);
        foreach ($groups as $v) {
            $data[] = $v['group_id'];
        }
        return $data;
    }

    public function getSubGroupsIds($uid = null, $withself = false)
    {
        $groups = $this->getGroups($uid);
        $ids = [];
        $tree = $this->getGroupTree();
        foreach ($groups as $v) {
            $children = $tree->getChildrenIds($v['group_id'], $withself);
            $ids = array_merge($ids, $children);
        }
        return $ids;
    }

    public function getGroupTree()
    {
        if (!$this->tree) {
            $list = $this->static::whereRaw("1=1")->get()->toArray();
            $this->tree = Tree::instance();
            $this->tree->init($list, 'id', 'pid');
        }
        return $this->tree;
    }

}