<?php

namespace app\system\model\admin;

class UserMoneyLogModel extends Model 
{
    protected $table = "user_money_log";

    const UPDATED_AT = NULL;

    
    public function user()
    {
        return $this->belongsTo(UserModel::class);
    }

}