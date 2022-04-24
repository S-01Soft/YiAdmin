<?php

namespace app\system\validate\admin;

class AuthRuleValidate extends Validate
{
    public function __construct()
    {
        $this->field = [
            'type' => lang('Type'),
            'pid' => lang('Pid'),
            'name' => lang('Name'),
            'title' => lang('Title'),
            'icon' => lang('Icon'),
            'condition' => lang('Condition'),
            'remark' => lang('Remark'),
            'ismenu' => lang('Is Menu'),
            'weigh' => lang('Weigh'),
            'status' => lang('Status'),
            'app' => lang('App'),
            'parent_rule' => lang('Parent'),
            'app_type' => lang('App Type'),
        ];
    }
    
    protected $rule =  [
        'type' => 'require',
        'pid' => 'require',
        'name' => 'require|length:0,100',
        'title' => 'require|length:0,50',
        'icon' => 'require|length:0,50',
        'condition' => 'require|length:0,255',
        'remark' => 'require|length:0,255',
        'ismenu' => 'require',
        'weigh' => 'require',
        'app' => 'require|length:0,255',
        'parent_rule' => 'require|length:0,255',
        'app_type' => 'require|length:0,10',
        'status' => 'require|length:0,30',   
    ];
    
    protected $message  =  [];
    
    protected $scene = [
        'add' => ['type', 'pid', 'name', 'title', 'icon', 'condition', 'remark', 'ismenu', 'weigh', 'app', 'parent_rule', 'app_type', 'status'],
        'edit' => ['type', 'pid', 'name', 'title', 'icon', 'condition', 'remark', 'ismenu', 'weigh', 'app', 'parent_rule', 'app_type', 'status'],
    ];    
}