<?php

namespace app\system\logic\admin;

use app\system\model\admin\UserGroupAccessModel;

class UserLogic extends Logic
{
    protected $where_ignore = ['groups'];

    protected function initialize()
    {
        $this->static = \app\system\model\admin\UserModel::class;
        parent::initialize();
    }
    
    protected function beforePaginate($query)
    {
        $query->with(['groups']);
        if (isset($this->where['groups'])) {
            $groups = $this->where['groups'][1] ?? '';
            if (!empty($groups)) {
                $groups = is_array($groups) ? $groups : explode(',', $groups);
                $query->whereIn('id', function($q) use ($groups) {
                    $q->from('user_group_access')->select('uid')->whereIn('group_id', $groups);
                });
            }
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
        $ids = explode(',', $form['groups']);
        foreach ($ids as $id) {
            if (empty($id)) continue;
            (new UserGroupAccessModel)->insert([
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

    protected function afterPostEdit($model, $form = [])
    {
        $form = request()->post('form');
        if (empty($form['groups'])) return $model;
        $ids = explode(',', $form['groups']);
        UserGroupAccessModel::where('uid', $form['id'])->delete();
        foreach ($ids as $id) {
            if (empty($id)) continue;
            (new UserGroupAccessModel)->insert([
                'uid' => $form['id'],
                'group_id' => $id
            ]);
        }
        return $model;
    }

    protected function beforePostEdit($form, $query)
    {
        if (empty($form['password'])) unset($form['password']);
        unset($form['groups']);
        return $form;
    }

    public function money($attributes)
    {
        extract($attributes);
        $this->static::addMoney($userid, $value, $memo);
    }
    
    public function score($attributes)
    {
        extract($attributes);
        $this->static::addScore($userid, $value, $memo);
    }
}