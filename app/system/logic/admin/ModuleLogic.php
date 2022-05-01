<?php

namespace app\system\logic\admin;

use support\exception\Exception;
use yi\Module;
use yi\Http;
use yi\Auth;
use yi\Menu;
use yi\Storage;

class ModuleLogic extends Logic
{
    const LOGIN_URL = '/appstore/api/login';
    const LOGOUT_URL = '/appstore/api/logout';
    const GET_USERINFO_URL = '/appstore/api/get_userinfo';
    const DOWN_MODULE_URL = '/appstore/api/module/down';
    const MODULE_LIST_URL = '/appstore/api/modules';
    const MODULE_TAGS_URL = '/appstore/api/module/tags';

    public function upload()
    {
        $file = request()->file('file');
        $config = [
            'driver' => 'local',
            'record' => false,
            'type' => 'private'
        ];
        return Storage::config($config)->upload($file);
    }
    
    public function install()
    {
        $force = request()->post('force', false);
        $path = request()->post('path');
        try {
            $zip = new \ZipArchive();
            if ($zip->open($path) !== TRUE) throw new Exception(lang("Unable to open compressed package"));
            $info = parse_ini_string($zip->getFromName('info.ini'));
            if (empty($info['name'])) throw new Exception(lang('The file "info" parsing error'));
            $name = $info['name'];
            if (!$force && isset(get_full_module_list()[$name])) return ['code' => 1001, 'path' => $path, 'name' => $name];
            Module::install($path, $force);
            return [ 'code' => 1, 'data' =>  $name];
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function uninstall($name)
    {
        return Module::uninstall($name);
    }

    public function setState()
    {
        $params = request()->post();
        $name = $params['name'];
        $action = $params['status'] ? 'enable' : 'disable';
        Module::$action($name);
    }

    public function addOptionItem($name, $form)
    {
        if (!module_exists($name)) throw new Exception(lang('The module does not exists'));
        $config = get_module_full_config($name);
        if (isset($config[$form['name']])) throw new Exception(lang('Configuration already exists'));
        $config[$form['name']] = $form;
        $rs = set_module_full_config($name, $config);
    }

    public function editOptionItem($name, $form)
    {   
        if (!module_exists($name)) throw new Exception(lang('Module is not exists'));
        $config = get_module_full_config($name);
        $config[$form['name']] = $form;
        $rs = set_module_full_config($name, $config);
    }

    public function deleteOptionItem($name, $option_name)
    {
        if (!module_exists($name)) throw new Exception(lang('Module is not exists'));
        $config = get_module_full_config($name);
        unset($config[$option_name]);
        set_module_full_config($name, $config);
    }

    public function getTags()
    {
        $url = config('app.api_url') . static::MODULE_TAGS_URL . "?lang=" . request()->var('locale');
        $data = Http::get($url);
        return $data['data'];
    }

    public function getModuleList($type, $page = 1, $where = [])
    {
        switch ($type) {
            case 1:
                $list = get_module_list();
                $data = [
                    'total' => count($list),
                    'data' => $list
                ];
            break;
            case 2:
                $url = config('app.api_url') . static::MODULE_LIST_URL;
                $params = [
                    'lang' => request()->var('locale'),
                    'page' => $page,
                    'kw' => $where['kw'] ?? '',
                    'tag' => $where['tag'] ?? '',
                    'version' => get_version(),
                ];
                $data = Http::get($url, $params);
                $data = $data['data'];
            break;
        }
        return $data;
    }

    public function remoteInstall($form)
    {
        extract($form);
        $filepath = $this->down($name, $version);
        Module::install($filepath, true);
        @unlink($filepath);
        return $name;
    }

    protected function down($name, $version)
    {
        $user = get_admin();
        $form = [
            'name' => $name,
            'version' => $version,
            'token' => cache('SOFT_01_TOKEN' . $user->id)
        ];
        $content = Http::get(config('app.api_url') . static::DOWN_MODULE_URL, $form);
        if (is_string($content)) {
            $filepath = runtime_path() . DS . 'temp' . DS . 'modules' . DS . $name . '-' . $version . '-' . time() . '.zip';
            mkfile($filepath, $content);
            return $filepath;
        }
        elseif (is_array($content)) {
            throw new Exception($content['message']);
        }
        throw new Exception(lang('Remote server error, please try again'));
    }

    public function weigh($name, $weigh)
    {
        if (empty($weigh)) return false;
        $info = get_module_info($name);
        if (isset($info['sort']) && $info['sort'] == $weigh) return false;
        $info['sort'] = $weigh;
        set_module_info($name, $info);
        Menu::install($name, 'admin', 'admin');
    }

    public function getUserInfo()
    {
        try {
            $user = get_admin();
            var_dump(cache('SOFT_01_TOKEN' . $user->id));
            $data = Http::get(config('app.api_url') .  static::GET_USERINFO_URL, ['token' => cache('SOFT_01_TOKEN' . $user->id), 'lang' => request()->var('locale')]);
            return $data['data'];
        } catch (Exception $e) {
        }
    }

    public function login($form)
    {
        $user = get_admin();
        if (empty($form['code'])) unset($form['code']);
        $data = Http::post(config('app.api_url') . static::LOGIN_URL . '?lang=' . request()->lang, $form);
        if ($data['code'] != 1) throw new Exception($data['message']);
        if (!empty($data['data']['code'])) {
            if ($data['data']['code'] == 1) cache('SOFT_01_TOKEN' . $user->id, $data['data']['user']['token']);
        }
        return $data['data'];
    }

    public function logout()
    {
        $user = get_admin();
        Http::post(config('app.api_url') . static::LOGOUT_URL . '?lang=' . request()->var('locale'), ['token' => cache('SOFT_01_TOKEN' . $user->id)]);
        cache('SOFT_01_TOKEN' . $user->id, null);
    }
}