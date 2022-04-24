<?php

namespace app\system\model\admin;

use yi\Auth;
use support\Str;

class AdminModel extends Model 
{
    protected $table = "admin";

    protected $hidden = ['salt', 'password', 'token'];

    protected $dates = [''];
    
    public function getAvatarAttribute($value) 
    {
        return empty($value) ? fixurl('/static/images/missing-face.png') : fixurl($value);
    }

    public static function booted()
    {
        static::creating(function($user) {
            $user->salt = Str::random(6);
            $user->password = Auth::instance()->encryptPassword($user->password, $user->salt);
            $user->loginip = get_ip();
        });
        static::updating(function($user) {
            if ($user->isDirty('password')) {
                $user->password = Auth::instance()->encryptPassword($user->password, $user->salt);
            }
        });
    }

    public function groups()
    {
        return $this->belongsToMany(AuthGroupModel::class, 'auth_group_access', 'uid', 'group_id');
    }
}