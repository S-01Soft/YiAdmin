<?php

namespace app\system\validate\admin;

class SettingValidate extends Validate
{
    public function __construct()
    {
        $this->field = [
            'key' => lang('Key')
        ];
    }
    
    protected $rule =  [
        'key' => 'require|length:0,100',
    ];
    
    protected $message  =  [];
    
    protected $scene = [
        'add' => ['key'],
        'edit' => ['key'],
    ];    
}