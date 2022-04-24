<?php

namespace app\system\validate\admin;

class AuthGroupValidate extends Validate
{
    public function __construct()
    {
        $this->field = [
            'pid' => lang('Pid'),
            'name' => lang('Name'),
            'rules' => lang('Rules'),
            'status' => lang('Status')
        ];
    }
    
    protected $rule =  [
        'pid' => 'require',
        'name' => 'require|length:0,100',
        'rules' => 'require',
        'status' => 'require',   
    ];
    
    protected $message  =  [];
    
    protected $scene = [
        'add' => ['pid', 'name', 'rules', 'status'],
        'edit' => ['pid', 'name', 'rules', 'status'],
    ];    
}