<?php

namespace app\system\validate\admin;

class EventValidate extends Validate
{
    public function __construct()
    {
        $this->field = [
            'group' => lang('Group'),
            'event' => lang('Event'),
            'event_desc' => lang('Event Description'),
            'payload' => lang('Payload'),
        ];
    }

    protected $rule =  [
        'group' => 'require|length:0,50',
        'event' => 'require|length:0,50',
        'event_desc' => 'require|length:0,255',
        'payload' => 'length:0,500',   
    ];
    
    protected $message  =  [];
    
    protected $scene = [
        'add' => ['group', 'event', 'event_desc', 'payload'],
        'edit' => ['group', 'event', 'event_desc', 'payload'],
    ];    
}