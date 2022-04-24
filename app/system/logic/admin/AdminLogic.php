<?php

namespace app\system\logic\admin;

use think\Exception;
use yi\Auth;
use app\system\model\admin\AuthGroupAccessModel;

class AdminLogic extends Logic
{

    protected $where_ignore = ['groups'];

    protected function initialize()
    {
        $this->static = \app\system\model\admin\AdminModel::class;
        parent::initialize();
    }

    public function paginateView($c)
    {
        if (get_admin()->id != 1) $c->hidden = array_merge($c->hidden, ['delete_btn', 'slot_delete_btn']);
    }

    protected function beforePaginate($query)
    {
        $query->with(['groups']);
        $auth = get_admin();
        
        $groups = [];

        if ($auth->id != 1) $groups = AuthGroupLogic::instance()->getSubGroupsIds($auth->id);

        if (isset($this->where['groups'])) {
            $_groups = $this->where['groups'][1] ?? '';
            if (!empty($_groups)) {
                $_groups = is_array($_groups) ? $_groups : explode(',', $_groups);
                $groups = array_merge($groups, $_groups);
            }
        }
        
        if (!empty($groups)) {          
            $groups = is_array($groups) ? $groups : explode(',', $groups);
            $query->whereIn('id', function($q) use ($groups) {
                $q->from('auth_group_access')->select('uid')->whereIn('group_id', $groups);
            });
        }
    }

    protected function beforePostAdd($form = [])
    {
        if (isset($form['groups'])) unset($form['groups']);
        return $form;
    }

    protected function afterPostAdd($model, $form = [])
    {
        $form = request()->post('form');
        $this->checkGroup($form['groups']);
        $ids = explode(',', $form['groups']);
        foreach ($ids as $id) {
            if (empty($id)) continue;
            (new AuthGroupAccessModel)->insert([
                'uid' => $model->id, 'group_id' => $id
            ]);
        }
        return $model;
    }

    protected function beforeGetEdit($query)
    {
        $query->with(['groups']);
    }

    protected function afterGetEdit($model)
    {
        $model->setVisible(['id', 'avatar', 'nickname', 'username', 'status', 'email', 'mobile']);
        $groups = $model->groups->pluck('id')->toArray();
        $data = $model->toArray();
        $data['groups'] = implode(',', $groups);
        return $data;
    }

    protected function beforePostEdit($form, $query)
    {
        if (get_admin()->id != $form['id']) {
            $this->checkGroup($form['groups']);
            $this->checkOwner($form['id']);
        }
        if (empty($form['password'])) unset($form['password']);
        unset($form['groups']);
        return $form;
    }

    protected function afterPostEdit($model, $form = [])
    {
        $form = request()->post('form');
        if (empty($form['groups'])) return $model;
        $ids = explode(',', $form['groups']);
        AuthGroupAccessModel::where('uid', $form['id'])->delete();
        foreach ($ids as $id) {
            if (empty($id)) continue;
            (new AuthGroupAccessModel)->insert([
                'uid' => $form['id'],
                'group_id' => $id
            ]);
        }
        return $model;
    }

    protected function beforeToggle($model, $data)
    {
        $this->checkOwner($model->id);
        return $data;
    }

    protected function beforeDelete($query)
    {
        $query->where('id', '<>', 1);
        if (get_admin()->id != 1) throw new Exception(lang('Delete not allowed'));
        $cloneQuery = clone $query;
        AuthGroupAccessModel::whereIn('uid', $cloneQuery->pluck('id')->toArray())->delete();
    }

    protected function checkGroup($groups)
    {
        $auth = get_admin();
        if ($auth->id === 1) return true;
        $groups = is_string($groups) ? explode(',', $groups) : $groups;
        $my_groups = AuthGroupLogic::instance()->getGroups();
        $tree = AuthGroupLogic::instance()->getGroupTree();
        $ids = [];
        foreach ($my_groups as $group) {
            $children = $tree->getChildrenIds($group['group_id']);
            $ids = array_merge($ids, $children);
        }

        foreach ($groups as $id) {
            if (!in_array($id, $ids)) throw new Exception(lang("No permission to add the group"));
        }

        return true;
    }

    protected function checkOwner($uid)
    {
        $auth = get_admin();
        if ($auth->id == 1) return true;
        if ($auth->id == $uid) return true;
        
        $my_groups = AuthGroupLogic::instance()->getGroups();
        $tree = AuthGroupLogic::instance()->getGroupTree();
        $ids = [];
        foreach ($my_groups as $group) {
            $children = $tree->getChildrenIds($group['group_id']);
            $ids = array_merge($ids, $children);
        }
        
        $groups = $auth->getGroups($uid);
        foreach ($groups as $group) {
            if (in_array($group['group_id'], $ids)) {
                return true;
            }
        }
        
        throw new Exception(lang('No permission to modify'));
    }

}