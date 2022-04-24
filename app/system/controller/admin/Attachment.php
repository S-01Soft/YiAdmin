<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\controller\admin;

use support\exception\Exception;
use think\exception\ValidateException;

/**
 * @Menu(title=Attachments,weigh=75000,ignore=add|edit|tree|tree_list|tree_array|toggle|imports|exports|getAttributes|getScenes|getGroups,ismenu=1)
 */
class Attachment extends Base 
{
    protected $validate_cls = \app\system\validate\admin\AttachmentValidate::class;
    protected $has_tree = false;

    public $noNeedCheck = ['getAttributes', 'getScenes', 'getGroups'];
    
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\AttachmentLogic::instance(true);
    }

    /**
     * @Menu(title=Upload Attachment)
     */
    public function upload()
    {
        try {
            $file = request()->file('file');
            $form = request()->get();
            $url = $this->logic->upload($file, $form);
            return $this->success($url);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Get Attributes)
     */
    public function getAttributes()
    {
        try {
            $type = $this->request->get('type');
            $data = $this->logic->getAttributes($type);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Get Scene)
     */
    public function getScenes()
    {
        try {
            $data = $this->logic->getScenes();
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Get Groups)
     */
    public function getGroups()
    {
        try {
            $scene = $this->request->get('scene');
            $data = $this->logic->getGroups($scene);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Move Attachment)
     */
    public function move()
    {
        try {
            $params = $this->request->post();
            validate($this->validate_cls)->scene('move')->check($params);
            $this->logic->move($params);
            return $this->success();
        } catch (ValidateException $e) {            
            return $this->error($e->getMessage());
        }
        catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

}