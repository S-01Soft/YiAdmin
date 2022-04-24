<?php

namespace app\system\validate\admin;

class EventAppsValidate extends Validate
{
    public function __construct()
    {
        $this->field = [
            'class_name' => lang('Class Name'),
            'class_desc' => lang('Description'),
            'app_name' => lang('Plugin Name'),
            'event' => lang('Event'),
            'app_title' => lang('App'),
        ];
    }
    
    protected $rule =  [
        'class_name' => 'require|length:0,255',
        'class_desc' => 'require|length:0,255',
        'app_name' => 'length:0,255',
        'event' => 'length:0,255',
        'app_title' => 'length:0,255',   
    ];
    
    protected $message  =  [];
    
    protected $scene = [
        'add' => ['class_name', 'class_desc', 'app_name', 'event', 'app_title'],
        'edit' => ['class_name', 'class_desc', 'app_name', 'event', 'app_title'],
    ];    
}