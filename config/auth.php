<?php

return [
    'admin' => [
        'auth_on' => 1, // 权限开关
        'auth_type' => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_group' => 'auth_group', // 用户组数据表名
        'auth_group_access' => 'auth_group_access', // 用户-用户组关系表
        'auth_rule' => 'auth_rule', // 权限规则表
        'auth_user' => 'admin', // 用户信息表
        'user' => \app\system\model\admin\AdminModel::class,
        'allow_login_fail' => 3, // 最多登录失败次数
        'fail_time' => 24 * 60, // 登录失败到达次数后禁止登录时长（分钟）
        'username_fields' => 'username,email', // 可作为用户名登录的字段
    ],
    'user' => [
        'auth_on' => 1, // 权限开关
        'auth_type' => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_group' => 'user_group', // 用户组数据表名
        'auth_group_access' => 'user_group_access', // 用户-用户组关系表
        'auth_rule' => 'user_rule', // 权限规则表
        'auth_user' => 'user', // 用户信息表
        'user' => \app\system\model\admin\UserModel::class,
        'info' => ['id', 'uid', 'nickname', 'avatar', 'score', 'money', 'token'],
        'allow_login_fail' => 5, // 最多登录失败次数
        'fail_time' => 24 * 60, // 登录失败到达次数后禁止登录时长（分钟）
        'username_fields' => 'username,email,mobile', // 可作为用户名登录的字段
    ]
];