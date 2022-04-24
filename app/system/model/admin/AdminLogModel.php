<?php

namespace app\system\model\admin;

class AdminLogModel extends Model 
{
    protected $table = "admin_log";

    protected $primaryKey = 'id';

    protected $dateFormat = 'U';

}