<?php

namespace app\system\validate\admin;

class UserScoreLogValidate extends Validate
{
    public function __construct()
    {
        $this->field = [
            'user_id' => lang('User ID'),
            'score' => lang('Score'),
            'before' => lang('Before'),
            'after' => lang('After'),
            'memo' => lang('Remark')
        ];
    }

    protected $rule =  [
        'user_id' => 'require',
        'score' => 'require',
        'before' => 'require',
        'after' => 'require',
        'memo' => 'length:0,255',   
    ];
    
    protected $message  =  [];
    
    protected $scene = [
        'add' => ['user_id', 'score', 'before', 'after', 'memo'],
        'edit' => ['user_id', 'score', 'before', 'after', 'memo'],
    ];    
}