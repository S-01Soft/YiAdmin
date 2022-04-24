<?php

namespace app\system\model\admin;

class LogModel extends Model 
{
    protected $table = "log";

    const UPDATED_AT = null;

    protected $appends = ['ip_txt', 'user_agent_txt'];

    public function getTitleAttribute($value)
    {
        if (empty($value)) return $value;
        $array = explode('/', $value);
        $result = [];
        foreach ($array as $v) {
            $result[] = lang($v);
        }
        return implode('/', $result);
    }

    public function getUserAgentTxtAttribute($value)
    {
        $value = $this->attributes['useragent'] ?? '';
        return ev('ParseUserAgent', $value);
    }

    public function getIpTxtAttribute()
    {
        $value = $this->attributes['ip'] ?? '';
        return ev('ParseIp', $value);
    }
}