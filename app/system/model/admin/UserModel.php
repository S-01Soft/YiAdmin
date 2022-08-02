<?php

namespace app\system\model\admin;

use support\Str;
use yi\User;
use app\system\model\admin\UserScoreLogModel;
use app\system\model\admin\UserMoneyLogModel;

class UserModel extends Model 
{
    protected $table = "user";
    
    protected $hidden = ['salt', 'password', 'token'];

    public function getAvatarAttribute($value) 
    {
        return empty($value) ? '/static/images/missing-face.png' : $value;
    }

    public static function booted()
    {
        static::creating(function($user) {
            $auth = User::instance();
            $user->salt = Str::random(6);
            $user->password = $auth->encryptPassword($user->password, $user->salt);
            $user->uid = $auth->createUid();
            $user->loginip = get_ip();
        });
        static::updating(function($user) {
            if ($user->isDirty('password')) {
                $user->password = User::instance()->encryptPassword($user->password, $user->salt);
            }
            unset($user->uid);
        });
    }


    public function groups()
    {
        return $this->belongsToMany(UserGroupModel::class, 'user_group_access', 'uid', 'group_id');
    }

    
    public static function addScore($user_id, $score, $memo = '')
    {   
        $user = static::find($user_id);
        $data = [
            'user_id' => $user_id,
            'score' => $score,
            'before' => $user->score,
            'after' => $user->score + $score,
            'memo' => $memo
        ];
        $user->score = $user->score + $score;
        $user->save();
        UserScoreLogModel::create($data);
    }

    public static function addMoney($user_id, $money, $memo = '')
    {   
        $user = static::find($user_id);
        $data = [
            'user_id' => $user_id,
            'money' => $money,
            'before' => $user->money,
            'after' => $user->money + $money,
            'memo' => $memo,
            'created_at' => time()
        ];
        $user->money = $user->money + $money;
        $user->save();
        UserMoneyLogModel::create($data);
    }

}