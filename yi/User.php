<?php

namespace yi;

use support\Str;

class User extends \yi\library\Auth 
{
    protected $config = [
        'auth_on' => 1, // 权限开关
        'auth_type' => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_group' => 'user_group', // 用户组数据表名
        'auth_group_access' => 'user_group_access', // 用户-用户组关系表
        'auth_rule' => 'user_rule', // 权限规则表
        'auth_user' => 'user', // 用户信息表
        'user' => \app\system\model\admin\UserModel::class,
        'scene' => 'user',
        'info' => ['id', 'nickname', 'avatar', 'score', 'money', 'token'],
        'allow_login_fail' => 20, // 最多登录失败次数
        'fail_time' => 24 * 60, // 登录失败到达次数后禁止登录时长（分钟）
        'username_fields' => 'username,email,mobile', // 可作为用户名登录的字段
        'fail_time' => 24 * 60, // 登录失败到达次数后禁止登录时长（分钟）
    ];

    protected $isLogined = false;

    protected $_user = null;

    protected $_token = null;
    
    public function init($token)
    {   
        if ($this->isLogined) return true;
        $t = Token::init();

        $data = $t->get($token);
        if (!$data) return false;
        $user_id = intval($data['user_id']);
        if ($user_id > 0) {
            $user = $this->config['user']::find($user_id);
            if (!$user) {
                $this->setError(lang('The user does not exist'));
                return false;
            }
            if ($user['status'] != 1) {
                $this->setError(lang('The account is disabled'));
                return false;
            }
            $this->_user = $user;
            $this->isLogined = true;
            $this->_token = $token;
            return true;
        } else {
            $this->setError(lang('You are not logged in'));
            return false;
        }
        return true;
    }

    public function getInfo()
    {
        if (!$this->isLogin()) return null;
        $data = [];
        foreach ($this->config['info'] as $key) {
            $data[$key] = $this->getUser()[$key];
        }
        return $data;
    }


    public function register($username, $password, $email, $mobile, $nickname = null)
    {
        $data = [
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname ? $nickname :  'user-' . Random::numeric(10),
            'email' => $email,
            'mobile' => $mobile,
            'status' => 1
        ];
        $user = $this->config['user']::create($data);
        $payload = (object)[
            'user' => $user
        ];
        event(Str::studly($this->config['scene'] . '_register_success'), $payload);
        return $payload->user;
    }
}