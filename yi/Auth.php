<?php

namespace yi;

class Auth extends \yi\library\Auth 
{
    protected $config = [
        'auth_on' => 1, // 权限开关
        'auth_type' => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_group' => 'auth_group', // 用户组数据表名
        'auth_group_access' => 'auth_group_access', // 用户-用户组关系表
        'auth_rule' => 'auth_rule', // 权限规则表
        'auth_user' => 'admin', // 用户信息表
        'user' => \app\system\model\admin\AdminModel::class,
        'scene' => 'admin',
        'allow_login_fail' => 10, // 最多登录失败次数
        'fail_time' => 24 * 60, // 登录失败到达次数后禁止登录时长（分钟）
        'username_fields' => 'username', // 可作为用户名登录的字段
        'fail_time' => 24 * 60, // 登录失败到达次数后禁止登录时长（分钟）
    ];


    protected $isLogined = false;

    protected $_user = null;
    
}