<?php

namespace app\system\model\admin;

class UserRuleModel extends Model 
{
    protected $table = "user_rule";

    protected $primaryKey = 'id';

    protected $dateFormat = 'U';

    public function pidC()
    {
        return $this->belongsTo(\app\system\model\admin\UserRuleModel::class, 'pid', 'id');
    }

}