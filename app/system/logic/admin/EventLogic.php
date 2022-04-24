<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\logic\admin;

class EventLogic extends Logic
{

    protected function initialize()
    {
        $this->static = \app\system\model\admin\EventModel::class;
        parent::initialize();
    }

    public function paginateView($c) 
    {
        $c->hidden = ['add_btn', 'slot_edit_btn'];
    }

    protected function beforePaginate($query)
    {
        $query->with(['eventApp']);
    }

    public function refreshEvent()
    {
        app(\yi\EventLib::class)->clear();
    }

    public function reloadEvent($app = null)
    {
        $e = app(\yi\EventLib::class);
        if (!empty($app)) $e->enable($app);
        else {
            $apps = get_full_module_list();
            foreach ($apps as $app) {
                $e->enable($app['name']);
            }
        }
    }
    
}