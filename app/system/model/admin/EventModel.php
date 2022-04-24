<?php

namespace app\system\model\admin;

class EventModel extends Model 
{
    protected $table = "event";

    public $timestamps = false;

    
    public function eventApp()
    {
        return $this->hasMany(EventAppsModel::class, 'event', 'event');
    }
    

}