<?php

namespace app\system\model\admin;

class AuthGroupModel extends Model 
{
    protected $table = "auth_group";
    
    protected $guarded = [];

    public function pid()
    {
        return $this->belongsTo(\app\system\model\admin\AuthGroupModel::class, 'pid', 'id');
    }

}