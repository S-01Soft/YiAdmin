<?php

namespace app\system\model\admin;

class UserGroupModel extends Model 
{
    protected $table = "user_group";

    protected $primaryKey = 'id';

    protected $dateFormat = 'U';

    public function pidC()
    {
        return $this->belongsTo(\app\system\model\admin\UserGroupModel::class, 'pid', 'id');
    }

}