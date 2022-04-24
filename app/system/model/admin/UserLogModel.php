<?php

namespace app\system\model\admin;

class UserLogModel extends Model 
{
    protected $table = "user_log";

    public function getIpAttr($value, $data)
    {
        $result = ev('ParseIp', $value);
        if ($result) $value .= $result['country'] . $result['area'];
        return $value;
    }

}