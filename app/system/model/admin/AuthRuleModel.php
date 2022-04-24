<?php

namespace app\system\model\admin;

class AuthRuleModel extends Model 
{
    protected $table = "auth_rule";

    protected $primaryKey = 'id';

    protected $appends = [
        'title_txt'
    ];

    public function pidC()
    {
        return $this->belongsTo(\app\system\model\admin\AuthRuleModel::class, 'pid', 'id');
    }

    public function getTitleTxtAttribute($value)
    {
        return lang($this->attributes['title']);
    }

}