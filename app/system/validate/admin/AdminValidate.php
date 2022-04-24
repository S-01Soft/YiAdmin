<?php

namespace app\system\validate\admin;

class AdminValidate extends Validate
{
    public function __construct()
    {
        $this->field = [
            'username' => lang('Username'),
            'nickname' => lang('Nickname'),
            'password' => lang('Password'),
            'avatar' => lang('Avatar'),
            'email' => lang('Email'),
            'loginip' => lang('Login IP'),
            'status' => lang('Status')
        ];
    }
    
    protected $regex = [
        'password' => '[\w\.@]+'
    ];
    protected $rule =  [
        'username' => 'length:5,20|require',
        'nickname' => 'length:0,50|require',
        'password' => 'length:6,32|regex:password',
        'avatar' => 'length:0,255',
        'email' => 'length:0,100|email',
        'loginip' => 'length:0,50',
        'status' => 'require|length:0,30',   
    ];
    
    protected $scene = [
        'login' => ['username', 'password'],
        'add' => ['username', 'nickname', 'password', 'avatar', 'email', 'loginip', 'status'],
        'edit' => ['username', 'nickname', 'password', 'avatar', 'email', 'loginip', 'status'],
    ];    
}