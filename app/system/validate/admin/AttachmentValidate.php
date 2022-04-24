<?php

namespace app\system\validate\admin;

class AttachmentValidate extends Validate
{
    
    public function __construct()
    {
        $this->field = [
            'path' => lang('Path'),
            'filesize' => lang('File Size'),
            'mimetype' => lang('Mime Type'),
            'storage' => lang('Storage'),
            'scene' => lang('Scene'),
            'group' => lang('Group')
        ];
        //parent::__construct();
    }

    protected $rule =  [
        'admin_id' => 'require',
        'user_id' => 'require',
        'url' => 'require|length:0,255',
        'imagewidth' => 'require|length:0,30',
        'imageheight' => 'require|length:0,30',
        'imagetype' => 'require|length:0,30',
        'imageframes' => 'require',
        'filesize' => 'require',
        'mimetype' => 'require|length:0,100',
        'extparam' => 'require|length:0,255',
        'storage' => 'require|length:0,100',
        'md5' => 'require|length:0,40',
        'sha1' => 'require|length:0,40',
        'type' => 'require|length:0,55',
        'ids' => 'require',
        'scene' => 'require|length:1,55',
        'group' => 'require|length:1,55',
    ];
    
    protected $message  =  [];
    
    protected $scene = [
        'add' => ['admin_id', 'user_id', 'url', 'imagewidth', 'imageheight', 'imagetype', 'imageframes', 'filesize', 'mimetype', 'extparam', 'storage', 'md5', 'sha1', 'type', 'scene', 'group'],
        'edit' => ['admin_id', 'user_id', 'url', 'imagewidth', 'imageheight', 'imagetype', 'imageframes', 'filesize', 'mimetype', 'extparam', 'storage', 'md5', 'sha1', 'type', 'scene', 'group'],
        'move' => ['ids', 'scene', 'group']
    ];    
}