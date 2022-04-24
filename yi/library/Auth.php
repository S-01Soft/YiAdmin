<?php

namespace yi\library;

use support\Db;
use support\Container;
use support\Str;
use yi\Token;
use yi\Random;

class Auth
{
    //默认配置
    protected $config = [
        'auth_on' => 1, // 权限开关
        'auth_type' => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_group' => 'auth_group', // 用户组数据表名
        'auth_group_access' => 'auth_group_access', // 用户-用户组关系表
        'auth_rule' => 'auth_rule', // 权限规则表
        'auth_user' => 'auth_user', // 用户信息表
        'allow_login_fail' => 10, // 最多登录失败次数
        'fail_time' => 24 * 60, // 登录失败到达次数后禁止登录时长（分钟）
        'scene' => 'auth',
    ];

    protected $groups = [];

    protected $_auth_list = [];

    public function __construct()
    {
        if ($option = config('auth.' . $this->config['scene'])) {
            $this->config = array_merge($this->config, $option);
        }
        get_lang()->loadDirs([
            base_path() . DS . 'yi' . DS . 'lang' . DS . 'auth' . DS
        ]);
    }
    
    public static function instance()
    {
        $class = get_called_class();
        return new $class;
    }

    /**
     * 检查权限
     * @param $name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param $uid  int           认证用户的id
     * @param int $type 认证类型
     * @param string $mode 执行check的模式
     * @param string $relation 如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * return bool               通过验证返回true;失败返回false
     */
    public function check($name, $uid = null, $type = 1, $mode = '', $relation = 'or')
    {
        $uid = $uid ?: $auth->id;
        if (!$this->config['auth_on']) {
            return true;
        }
        if ($uid == 1) return true;
        // 获取用户需要验证的所有有效规则列表
        $authList = $this->getAuthList($uid, $type);
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        $list = []; //保存验证通过的规则名
        if ('url' == $mode) {
            $REQUEST = unserialize(strtolower(serialize(request()->get())));
        }
        
        foreach ($authList as $auth) {
            $query = preg_replace('/^.+\?/U', '', $auth);
            if ('url' == $mode && $query != $auth) {
                parse_str($query, $param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $auth = preg_replace('/\?.*$/U', '', $auth);
                if (in_array($auth, $name) && $intersect == $param) {
                    //如果节点相符且url参数满足
                    $list[] = $auth;
                }
            } else {
                if (in_array($auth, $name)) {
                    $list[] = $auth;
                }
            }
        }
        if ('or' == $relation && !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ('and' == $relation && empty($diff)) {
            return true;
        }
        return false;
    }
    /**
     * 根据用户id获取用户组,返回值为数组
     * @param  $uid int     用户id
     * return array       用户所属的用户组 array(
     *     array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     *     ...)
     */
    public function getGroups($uid)
    {
        if (isset($this->groups[$uid])) {
            return $this->groups[$uid];
        }
        // 转换表名
        $auth_group_access = $this->config['auth_group_access'];
        $auth_group = $this->config['auth_group'];
        // 执行查询
        $user_groups = Db::table($auth_group_access)->join($auth_group, "{$auth_group_access}.group_id", '=', "{$auth_group}.id")
            ->where("{$auth_group_access}.uid", '=', $uid)
            ->where("{$auth_group}.status", 1)
            ->get()->map(function($row) {
                return (array)$row;
            })->toArray();
        $this->groups[$uid] = $user_groups ?: [];
        return $this->groups[$uid];
    }
    /**
     * 获得权限列表
     * @param integer $uid 用户id
     * @param integer $type
     * return array
     */
    protected function getAuthList($uid, $type)
    {
        if (isset($this->_auth_list[$uid])) {
            return $this->_auth_list[$uid];
        }
        if (2 == $this->config['auth_type'] && session($this->config['scene'] . '_auth_list_' . $uid)) {
            return session($this->config['scene'] . '_auth_list_' . $uid);
        }
        //读取用户所属用户组
        $groups = $this->getGroups($uid);
        $ids = []; //保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g) {
            if (!empty($g['rules']))
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $this->_auth_list[$uid] = [];
            return [];
        }
        //读取用户组所有权限规则
        $rules = Db::table($this->config['auth_rule'])->whereIn('id', $ids)->selectRaw('`condition`,`name`')->get()->toArray();
        //循环规则，判断结果。
        $authList = []; //
        foreach ($rules as $rule) {
            if (!empty($rule->condition)) {
                //根据condition进行验证
                $user = $this->getUserInfo($uid); //获取用户信息,一维数组
                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule->condition);

                @(eval('$condition=(' . $command . ');'));
                if ($condition) {
                    $authList[] = strtolower($rule->name);
                }
            } else {
                //只要存在就记录
                $authList[] = strtolower($rule->name);
            }
        }
        $this->_auth_list[$uid] = $authList;
        if (2 == $this->config['auth_type']) {
            //规则列表结果保存到session
            session()->set($this->config['scene'] . '_auth_list_' . $uid, $authList);
        }
        return array_unique($authList);
    }
    /**
     * 获得用户资料,根据自己的情况读取数据库 
     */
    function getUserInfo($uid)
    {
        static $userinfo = [];
        $user = Db::table($this->config['auth_user']);
        // 获取用户表主键
        $_pk = is_string($user->getPk()) ? $user->getPk() : 'uid';
        if (!isset($userinfo[$uid])) {
            $userinfo[$uid] = $user->where($_pk, $uid)->first();
        }
        return $userinfo[$uid];
    }

    //根据uid获取角色名称
     function getRole($uid){
        try{
            $gid =  Db::table($this->config['auth_group_access'])->where('uid',$uid)->first()->group_id;
            $name =  Db::table($this->config['auth_group'])->where('id',$gid)->first()->name;
            return $name;
        }catch (\Exception $e){
            throw new Exception("此用户未授予角色");
        }

    }
    /**
     * 授予用户权限
     */
    public function setRole($uid,$group_id){
        $res =  Db::table($this->config['auth_group_access'])
            ->where('uid',$uid)
            ->update(['group_id'=>$group_id]);
        return true;
    }

    public function encryptPassword($password, $salt)
    {
        return md5(md5($password) . $salt);
    }

    public function isLogin()
    {
        if (session($this->config['scene'])) return true;
        return false;
    }
    
    public function beLogin($name, $pwd)
    {
        $query = $this->config['user']::whereRaw("1=0");
        $fields = $this->config['username_fields'];
        $fields = is_array($fields) ? $fields : explode(',', $fields);
        foreach ($fields as $field) {
            $query->orWhere($field, $name);
        }
        $user = $query->first();
        if (!$user) {
            $this->setError(lang("The user does not exist"));
            return false;
        }

        if ($user->status != 1) {
            $this->setError(lang("The account is disabled"));
            return false;
        }
        
        if ($user->loginfailure >= $this->config['allow_login_fail'] && $user->updated_at->timestamp + $this->config['fail_time'] * 60 > time()) {
            $time = ceil(($this->config['fail_time'] * 60 + $user->updated_at->timestamp - time()) / 60);
            $this->setError(lang("Your login failure has reached the limit, Please try again in :1 minutes", [':1' => $time]));
            return false;
        }

        if ($user->password != $this->encryptPassword($pwd, $user->salt)) {
            $user->loginfailure += 1;
            $user->save();
            $this->setError(lang("Password Error"));
            return false;
        }
        $user->logintime = time();
        $user->save();

        return $user;
    }

    
    public function login($name, $pwd)
    {
        if ($user = $this->beLogin($name, $pwd)) {
            $this->direct($user);
            return true;
        }
        return false;
    }
    
    public function direct($data)
    {
        if ($data instanceof $this->config['user']) {
            $user = $data;
        } else $user = $this->config['user']::find($data);
        if ($user) {
            $user->loginip = get_ip();
            $user->logintime = time();
            $user->loginfailure = 0;
            $this->_token = Random::uuid();
            $user->token = $this->_token;
            session()->set($this->config['scene'], $user->toArray());
            $this->_user = $user;
            $this->initToken()->set($this->_token, $this->_user->id);
            $payload = (object) ['user' => $user];
            event(Str::studly($this->config['scene'] . "_login_success"), $payload);
            $user->save();
            return true;
        }
        return false;
    }

    public function initToken()
    {
        return Token::init([
            'scene' => $this->config['scene']
        ]);
    }

    public function logout()
    {
        if (!$this->isLogin()) {
            $this->setError(lang("You are not logged in"));
            return false;
        }

        //设置登录标识
        session()->delete($this->config['scene']);
        //删除Token
        $this->initToken()->delete($this->_token);
        //退出成功的事件
        $payload = (object)['user' => $this->_user];
        event(Str::studly($this->config['scene'] . "_logout_success"), $payload);
        return true;
    }

    
    public function getUser()
    {
        if (!$this->isLogin()) return null;
        if (!$this->_user) $this->_user = $this->config['user']::find(session($this->config['scene'])['id']);
        return $this->_user;
    }
    
    public function __get($key)
    {
        return $this->getUser()[$key] ?? null;
    }


    protected function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }
}