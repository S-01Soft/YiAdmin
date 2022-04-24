<?php

namespace yi;

use support\exception\Exception;
use support\Db;
// use think\facade\Filesystem;
use support\Cache;
// use think\facade\Console;
use support\Str;
use yi\EventLib;
use yi\Http;

class Addon 
{
    const ERR_MODULE_EXSISTS = 1001;

    public static function loadlang()
    {
        $lang = request()->var('lang');
        $lang->loadDirs([
            base_path() . DS . 'yi' . DS . 'lang' . DS . 'module' . DS
        ]);
    }

    public static function exsists($path, &$name)
    {
        static::loadlang();
        $zip = new \ZipArchive();
        try {
            if ($zip->open($path) !== TRUE) throw new Exception(lang("Unable to open compressed package"));
            $info = parse_ini_string($zip->getFromName('info.ini'));
            if (empty($info['name'])) throw new Exception(lang('The file "info" parsing error'));
            $name = $info['name'];
            $module_path = app_path() . DS . $name . DS;
            return is_dir($module_path) ? $name : false;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function install($path, $force = false)
    {
        static::loadlang();
        $zip = new \ZipArchive();
        try {
            if ($zip->open($path) !== TRUE) throw new Exception(lang("Unable to open compressed package"));
            $name = null;
            static::exsists($path, $name);
            $module_path = app_path() . DS . $name . DS;
            if (is_dir($module_path)) {
                if (!$force)
                    throw new Exception(lang("The module is exsist"), static::ERR_MODULE_EXSISTS);
                $config = get_module_full_config($name);
                run_command('module', "--name $name --action disable");
                $upgrade = true;
            }

            @mkdir($module_path, 0755, true);
            try {
                $zip->extractTo($module_path);
            } catch (\ZipException $e) {
                if (!empty($upgrade))static::backup($name);
                @unlink($module_path);
                throw new Exception($e->getMessage());
            }

            Db::startTrans();
            try {
                static::exec_install_sql($name);
                static::copyThemes($name);
                static::extracts($name);
                if (!empty($upgrade)) {
                    static::mergeConfig($name, $config);
                }
                run_command('module', "--name $name --action install");
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                if (!empty($upgrade))static::backup($name);
                @rmdirs($module_path);
                throw new Exception($e->getMessage());
            }

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        @unlink($path);
        event('RefreshLangVersion');
        return $name;
    }

    public static function enable($name) 
    {
        run_command('module', "--name $name --action enable");
        $info = get_module_info($name);
        $info['status'] = 1;
        set_module_info($name, $info);
        $dir = app_path() . DS . $name . DS . 'public';
        if (is_dir($dir)) copy_files($dir, base_path() . DS . 'public');
        EventLib::instance()->enable($name);
        event('RefreshLangVersion');
        return true;
    }

    public static function disable($name)
    {
        run_command('module', "--name $name --action disable");
        $info = get_module_info($name);
        $info['status'] = 0;
        set_module_info($name, $info);
        EventLib::instance()->disable($name);
        event('RefreshLangVersion');
        return true;
    }

    public static function uninstall($name)
    {
        static::loadlang();
        if (!module_exists($name)) return false;
        $info = get_module_info($name);
        if ($info['status'] != 0) throw new Exception(lang("Please disable the plugin first"));

        run_command('module', "--name $name --action uninstall");
        $dirs = ['public/modules', 'public/static/js'];
        foreach ($dirs as $dir) {
            $d = base_path() . DS . $dir . DS . $name;
            if (is_dir($d)) rmdirs($d, true);
        }
        rmdirs(app_path() . DS . $name);
        static::removeThemes($name);
        EventLib::instance()->uninstall($name);
        app(\yi\Module::class)->refreshInfo()->refreshConfig();
        event('RefreshLangVersion');
        return true;
    }

    public static function down($name, $version)
    {
        $user = get_admin();
        $config = get_module_config('system');
        $form = [
            'name' => $name,
            'version' => $version,
            'token' => cache('soft-token-' . $user->id)
        ];
        $content = Http::get(config('app.api_url') . '/appstore/api/down', $form);
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

    public static function extracts($name)
    {
        $extracts = app_path() . DS . $name . DS . 'extracts' . DS;
        if (!is_dir($extracts)) return false;
        $dirs = scandir($extracts);
        foreach ($dirs as $dir) {
            if (in_array($dir, ['.', '..'])) continue;
            if (!is_dir(app_path() . DS . $dir)) continue;
            copy_files($extracts . $dir, app_path() . DS . $dir . DS);
        }
    }

    public static function exec_install_sql($name)
    {
        $base_path = app_path() . DS . $name . DS . 'install' . DS;
        if (!is_dir($base_path)) return;
        $history = Db::table('upgrades')->where('app', $name)->select('name')->get()->toArray();
        $history = array_column($history, 'name');
        foreach (scandir($base_path) as $name) {
            if (in_array($name, ['.', '..'])) continue;
            if (!Str::endsWith($name, '.sql')) continue;
            $list[] = substr($name, 0, strlen($name) - 4);
        }
        $list = version_sort($list);
        foreach ($list as $name) {
            $filename = $name . '.sql';
            $file = $base_path . $name . '.sql';
            if (file_exists($file) && !in_array($filename, $history)) {
                $sqls = split_sql($file);
                foreach ($sqls as $sql)
                {
                    Db::execute($sql);
                }

                $data = [
                    'app' => $name,
                    'name' => $filename,
                    'version' => $info['version'],
                    'created_at' => time()
                ];
                Db::table('upgrades')->save($data);
            }
        }
    }

    public static function copyThemes($name)
    {        
        $module_path = app_path() . DS . $name . DS;
        if (is_dir($module_path . 'template')) {
            $themes = array_diff(scandir($module_path . 'template'), ['.', '..']);
            foreach ($themes as $theme) {
                copy_files($module_path . 'template' . DS . $theme . DS, view_path() . DS . $theme . DS . $name . DS);
            }
        }
    }

    public static function removeThemes($name)
    {
        $themes = array_diff(scandir(view_path()), ['.', '..']);
        foreach ($themes as $theme) {
            $dir = view_path() . DS . $theme . DS . $name . DS;
            if (is_dir($dir)) rmdirs($dir);
        }
    }

    protected static function mergeConfig($name, $config)
    {
        $result = [];
        event('CloseOPcache');
        $data = get_module_full_config($name, true, true);
        foreach ($data as $key => $item) {
            if (isset($config[$key])) $item['value'] = $config[$key]['value'];
            $result[$key] = $item;
        }
        foreach ($config as $key => $item) {
            if (!isset($result[$key])) $result[$key] = $item;
        }
        set_module_full_config($name, $result);
    }

    public static function backup($name)
    {
        
        // Console::call('module', ["-a $name", "-c package", "--backup=1"]);
    }
}