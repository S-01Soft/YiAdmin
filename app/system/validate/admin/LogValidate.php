<?php

namespace app\system\validate\admin;

class LogValidate extends Validate
{
    protected $rule =  [
        'user_id' => 'require',
        'username' => 'length:0,30',
        'url' => 'length:0,1500',
        'title' => 'length:0,100',
        'content' => 'require',
        'method' => 'require|length:0,10',
        'type' => 'require|length:0,10',
        'app' => 'require|length:0,20',
        'useragent' => 'length:0,255',
        'ip' => 'length:0,50',   
    ];
    
    protected $message  =  [];
    
    protected $scene = [
        'add' => ['user_id', 'username', 'url', 'title', 'content', 'method', 'app_type', 'app', 'useragent', 'ip'],
        'edit' => ['user_id', 'username', 'url', 'title', 'content', 'method', 'app_type', 'app', 'useragent', 'ip'],
    ];    
}