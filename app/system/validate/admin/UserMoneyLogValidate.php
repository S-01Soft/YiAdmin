<?php

namespace app\system\validate\admin;

class UserMoneyLogValidate extends Validate
{
    public function __construct()
    {
        $this->field = [
            'user_id' => lang('User ID'),
            'money' => lang('Money'),
            'before' => lang('Before'),
            'after' => lang('After'),
            'memo' => lang('Remark')
        ];
    }
    
    protected $rule =  [
        'user_id' => 'require',
        'money' => 'require',
        'before' => 'require',
        'after' => 'require',
        'memo' => 'length:0,255',   
    ];
    
    protected $message  =  [];
    
    protected $scene = [
        'add' => ['user_id', 'money', 'before', 'after', 'memo'],
        'edit' => ['user_id', 'money', 'before', 'after', 'memo'],
    ];    
}