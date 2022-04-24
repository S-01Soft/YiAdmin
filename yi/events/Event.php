<?php

namespace yi\events;

class Event 
{
    public function onSetAdminRenderOption($payload)
    {
        $payload->options['view_path'] = app_path() . DS . request()->getModule() . DS . 'view' . DS;
    }
    
    public function onSetIndexRenderOption($payload)
    {
        $payload->options['view_path'] = base_path() . DS . 'view' . DS . get_current_theme() . DS;
    }
}