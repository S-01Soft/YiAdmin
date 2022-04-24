<?php

namespace app\system\validate\admin;

class UserGroupValidate extends Validate
{
    public function __construct()
    {
        $this->field = [
            'pid' => lang('Pid'),
            'name' => lang('Name'),
            'status' => lang('Status')
        ];
    }
    
    protected $rule =  [
        'pid' => 'require',
        'name' => 'require|length:0,100',
        'status' => 'require',   
    ];
    
    protected $message  =  [];
    
    protected $scene = [
        'add' => ['pid', 'name', 'status'],
        'edit' => ['pid', 'name', 'status'],
    ];    
}