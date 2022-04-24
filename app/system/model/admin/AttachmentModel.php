<?php

namespace app\system\model\admin;

use yi\Upload;

class AttachmentModel extends Model 
{
    protected $table = "attachment";

    protected $primaryKey = 'id';

    protected $dateFormat = 'U';
    protected $guarded = [];
    protected $appends = ['show_size'];

    public function admin()
    {
        return $this->belongsTo(AdminModel::class, 'admin_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }

    public function getShowSizeAttribute($value)
    {
        $data = $this->attributes;
        if (empty($data['filesize'])) return '0kb';
        $v = $data['filesize'];
        if ($v < 1024) return $v . 'b';
        if ($v < 1024 * 1024) return number_format($v / 1024, 2). 'kb';
        else return number_format($v / 1024 / 1024, 2) . 'mb';
    }
}