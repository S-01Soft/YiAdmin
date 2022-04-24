<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\logic\admin;

class UserGroupLogic extends Logic
{

    protected function initialize()
    {
        $this->static = \app\system\model\admin\UserGroupModel::class;
        parent::initialize();
    }

    protected function beforePaginate($query)
    {
        $query->with(['pid_c']);
    }
    protected function afterPaginate($result)
    {
        foreach($result as $row) {
            $row->visible(['id','pid','name','rules','created_at','updated_at','status','pid_c']);
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
            $row->visible(['id','pid','name','rules','created_at','updated_at','status','pid_c']);
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
            $row->visible(['id','pid','name','rules','created_at','updated_at','status','pid_c']);
            if($row->pid_c) $row->pid_c->visible(['id','name']);
        }
        return $result;
    }

    protected function beforePostAdd($form)
    {
        $form['rules'] = is_array($form['rules']) ? implode(',', $form['rules']) : $form['rules'];
        return $form;
    }

    protected function afterGetEdit($data)
    {
        $data['rules'] = array_map('intval', explode(',', $data['rules']));
        return $data;
    }

    protected function beforePostEdit($form, $model)
    {
        $form['rules'] = is_array($form['rules']) ? implode(',', $form['rules']) : $form['rules'];
        return $form;
    }
}