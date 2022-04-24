<?php

namespace app\system\model\admin;

class ApiLogModel extends Model 
{
    protected $table = "api_log";

    protected $primaryKey = 'id';

    protected $dateFormat = 'U';

}