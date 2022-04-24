<?php

namespace app\system\logic\admin;

use support\exception\Exception;
use yi\Verify;
use app\system\model\admin\LogModel;

class IndexLogic extends Logic
{
    const LOGIN_OK = 1;
    const BE_LOGIN = 2;

    public function login($form)
    {
        $config = get_module_full_config('system');
        $value = $config['login_validate']['value'];
        $types = $config['login_validate']['options'];
        if (empty($value)) {
            $res = get_admin()->login($form['username'], $form['password']);
            if ($res) return ['code' => static::LOGIN_OK];
            else throw new Exception(get_admin()->getError());
        }
        if (!in_array($form['type'], $value)) throw new Exception('Unsupported authentication method');
        $user = get_admin()->beLogin($form['username'], $form['password']);
        if ($user === false) throw new Exception(get_admin()->getError());

        
        if (!empty($form['code'])) {
            switch ($form['type']) {
                case 'email':
                    $name = $user->email;
                break;
                case 'sms':
                    $name = $user->mobile;
                break;
            }
            $res = Verify::check($name, $form['code'], $form['type'], 'admin_login');
            if ($res == true) get_admin()->direct($user);
            return ['code' => static::LOGIN_OK];
        }

        $message = "";
        switch ($form['type']) {
            case 'email':
                $name = $user->email;
                if (empty($name)) throw new Exception(lang('Your email is empty, unable to accept the verification code'));
                preg_match("/.+?(?=@)/", $name, $res);
                $str = $res[0];
                $replace = substr($str, 0, 1);
                $replace = ($replace . '***' . substr($str, -1, 1));
                $water_email = preg_replace("/.+?(?=@)/", $replace, $name);
                $message = lang('We have sent the verification code to your email %s, please check', ['%s' => $water_email]);
            break;
            case 'sms':
                $name = $user->mobile;
                if (empty($name)) throw new Exception(lang('Your phone number is empty, unable to accept the verification code'));
                $message = lang('SMS verification code has been sent, please check');
            break;
        }        

        if (!Verify::send($name, $form['type'], null, 'admin_login')) throw new Exception(lang('Verification code send fail'));
        return ['code' => static::BE_LOGIN, 'message' => $message];
    } 
    
    
    public function getStatisticsInfo($form)
    {     
        $data = [];
        $query = LogModel::where('type', 'index')->where('created_at', '>=', $form['start'])->where('created_at', '<=', $form['end']);

        $pvQuery = clone $query;
        $pvQuery->where('ajax', 0)->selectRaw('count(*) as total,from_unixtime(created_at, "%Y-%m-%d") as day')->groupBy('day');   
        $list = $pvQuery->get()->toArray();
        $data['pv'] = [
            'title' => array_column($list, 'day'),
            'value' => array_column($list, 'total')
        ];

        $ipQuery = clone $query;
        $ipQuery->where('ajax', 0)->selectRaw('count(distinct ip) as total,from_unixtime(created_at, "%Y-%m-%d") as day')->groupBy('day');
        $list = $ipQuery->get()->toArray();

        $data['ip'] = [
            'title' => array_column($list, 'day'),
            'value' => array_column($list, 'total')
        ];


        $indexPageTop10Query = clone $query;
        $indexPageTop10Query->where('ajax', 0)->selectRaw('count(*) as total,url')->groupBy('url')->orderByRaw('total DESC')->limit(10);
        $list = $indexPageTop10Query->get()->toArray();
        $data['indexPageTop10'] = $list;

        $indexUrlTop10Query = clone $query;
        $indexUrlTop10Query->selectRaw('count(*) as total,url')->groupBy('url')->orderByRaw('total DESC')->limit(10);
        $list = $indexUrlTop10Query->get()->toArray();
        $data['indexUrlTop10'] = $list;

        $indexIpTop10Query = clone $query;
        $indexIpTop10Query->selectRaw('count(*) as total,ip')->groupBy('ip')->orderByRaw('total DESC')->limit(10);
        $list = $indexIpTop10Query->get()->toArray();
        $data['indexIpTop10'] = $list;

        return $data;
    }
}