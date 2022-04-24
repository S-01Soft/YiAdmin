<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\logic\admin;

class EventAppsLogic extends Logic
{

    protected function initialize()
    {
        $this->static = \app\system\model\admin\EventAppsModel::class;
        parent::initialize();
    }

    public function paginateView($c) 
    {
        $c->hidden = ['add_btn', 'slot_edit_btn'];
    }

    protected function beforePaginate($query)
    {
        $event = request()->get('event');
        if (!empty($event)) $query->where('event', $event);
        return $query;
    }
}