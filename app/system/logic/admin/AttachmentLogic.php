<?php
/*
 * @Author: 01Soft
 * @Website: https://www.01soft.top
 * @Date: 2021-03-08
 * @LastEditors: 01Soft
 */

namespace app\system\logic\admin;

use yi\Storage;

class AttachmentLogic extends Logic
{

    protected function initialize()
    {
        $this->static = \app\system\model\admin\AttachmentModel::class;
        parent::initialize();
    }

    public function paginateView($c)
    {
        $c->hidden = ['add_btn', 'slot_edit_btn'];
    }

    protected function beforePaginate($query) 
    {
        $form = request()->get();
        $query->with(['user', 'admin']);
        if (!empty($form['scene'])) $query->where('scene', $form['scene']);
        if (!empty($form['group'])) $query->where('group', $form['group']);
    }

    public function getAttributes($type = null)
    {
        $rtn = [];
        $query = $this->static::whereRaw("1=1");
        if ($type) $query->where('type', $type);
        $sceneQuery = clone $query;
        $rtn['scenes'] = $sceneQuery->select('scene')->groupBy('scene');
        rsort($rtn['scenes']);
        $groupQuery = clone $query;
        $rtn['groups'] = $groupQuery->select('group')->groupBy('group');
        rsort($rtn['groups']);
        return $rtn;
    }

    public function getGroups($scene = '')
    {
        $query = $this->static::whereRaw("1=1");
        if (!empty($scene)) $query->where('scene', $scene);
        $data = $query->select('group')->distinct(true)->get();
        return $data;
    }

    public function getScenes()
    {
        $query = $this->static::whereRaw("1=1");
        $data = $query->select('scene')->distinct(true)->get();
        return $data;
    }

    public function move($attributes)
    {
        extract($attributes);
        return $this->static::whereIn('id', $ids)->update(['group' => $group, 'scene' => $scene]);
    }
    
    public function upload($file, $form = [])
    {
        $config = get_module_group_config('system', 'upload');
        $option = [
            'type' => 'public',
            'scene' => empty($form['scene']) ? '系统' : $form['scene'],
            'group' => empty($form['group']) ? '默认' : $form['group'],
            'accept' => $config['accept'],
            'maxsize' => $config['maxsize']
        ];
        return Storage::config($option)->upload($file);
    }

    protected function beforeSelect($query) 
    {
        $form = request()->get();
        $query->with(['user', 'admin'])->where('type', 'public');
        if (!empty($form['scene'])) $query->where('scene', $form['scene']);
        if (!empty($form['group'])) $query->where('group', $form['group']);
        if (request()->get('accept')) {
            $accept = request()->get('accept');
            if (strpos($accept, '*') === false) {
                $query->whereIn('ext', explode(',', str_replace('.', '', $accept)));
            }
        }
    }

    protected function beforeDelete($query)
    {
        $cloneQuery = clone($query);
        $this->deleteList = $cloneQuery->get()->toArray();
    }

    protected function afterDelete()
    {
        foreach ($this->deleteList as $attachment) {
            Storage::config(['driver' => $attachment['storage']])->delete($attachment);
        }
    }
}