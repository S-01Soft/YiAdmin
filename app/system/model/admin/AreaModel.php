<?php

namespace app\system\model\admin;

class AreaModel extends Model 
{
    protected $table = "area";

    protected $primaryKey = 'id';

    

    public function pidC()
    {
        return $this->belongsTo(\app\system\model\admin\AreaModel::class, 'pid', 'id');
    }

}