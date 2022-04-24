<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

use support\exception\Exception;

/**
 * @Menu(title=Events,weigh=60000,ignore=tree|tree_list|tree_array|add|edit|imports|exports|select|all|toggle,ismenu=1)
 */
class Event extends Base 
{
    protected $validate_cls = \app\system\validate\admin\EventValidate::class;
    protected $has_tree = false;
    
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\EventLogic::instance(true);
    }

    /**
     * @Menu(title=Refresh Events)
     */
    public function refresh()
    {
        try {
            $this->logic->refreshEvent();
            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
    /**
     * @Menu(title=Reload Events)
     */
    public function reload()
    {
        try {
            $app = request()->post('app');
            $this->logic->reloadEvent($app);
            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}