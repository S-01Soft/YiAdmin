<?php

namespace app\system\model\admin;

class UserScoreLogModel extends Model 
{
    protected $table = "user_score_log";
    
    const UPDATED_AT = NULL;
    
    public function user()
    {
        return $this->belongsTo(UserModel::class);
    }

}