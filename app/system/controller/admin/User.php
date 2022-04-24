<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

/**
 * @Menu(title=Users,weigh=77000,ignore=tree|tree_list|tree_array|imports|exports,ismenu=1)
 */
class User extends Base 
{
    protected $validate_cls = \app\system\validate\admin\UserValidate::class;
    protected $has_tree = false;
    
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\UserLogic::instance(true);
    }

    /**
     * @Menu(title=Change Money)
     */
    public function money()
    {
        try {
            $form = $this->request->post('form');
            $this->logic->money($form);
            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Change Score)
     */
    public function score()
    {
        try {
            $form = $this->request->post('form');
            $this->logic->score($form);
            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}