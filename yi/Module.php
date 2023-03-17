<?php

namespace yi;

use support\exception\Exception;
use support\Db;
use support\Str;
use yi\EventLib;

class Module 
{
    protected $infos = [];
    protected $configs = [];

    public function getInfo($name)
    {
        return $this->infos[$name] ?? [];
    }

    public function getFullConfig($name)
    {
        return $this->configs[$name];
    }

    public function getConfig($name)
    {
        $result = [];
        $config = $this->getFullConfig($name);
        foreach ($config as $v) {
            $result[$v['name']] = $v['value'];
        }
        return $result;
    }

    public function getGroupConfig($name, $group = null, $key = null)
    {
        $full_config = $this->getFullConfig($name);
        $config = [];
        if ($group) {
            foreach ($full_config as $item) {
                if (isset($item['group_key']) && $item['group_key'] == $group) $config[$item['alias']] = $item['value'];
            }
        } else {
            foreach ($full_config as $item) {
                if (!isset($item['group_key'])) continue;
                if (isset($config[$item['group_key']])) $config[$item['group_key']][$item['alias']] = $item['value'];
                else $config[$item['group_key']] = [$item['alias'] => $item['value']];
            }
        }
        if ($key) return $config[$key];
        return $config;
    }

    public function setInfo($name, $info)
    {
        $file = app_path() . DS . $name . DS . 'info.ini';
        if (file_exists($file)) {
            write_ini_file($info, $file);
            $this->infos[$name] = $info;
            ev('SyncData', __CLASS__, 'syncInfos');
            return true;
        }
        return false;
    }

    public function syncInfo($name, $info)
    {
        $this->infos[$name] = $info;
    }

    public function syncConfig($name, $config)
    {
        $this->configs[$name] = $config;
    }

    public function setFullConfig($name, $config)
    {
        $info = $this->getInfo($name);
        if (empty($info)) return false;
        $file = app_path() . DS . $name . DS . 'config.json';
        mkfile($file, json_encode($config, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        $this->configs[$name] = $config;
        ev('SyncData', __CLASS__, 'syncConfigs');
        return true;
    }

    public function setConfig($name, $form)
    {
        $info = $this->getInfo($name);
        if (empty($info)) return false;
        $file = app_path() . DS . $name . DS . 'config.json';
        $config = $this->getFullConfig($name);
        $result = [];
        foreach ($config as $k => $v) {
            $v['value'] = array_key_exists($v['name'], $form) ? $form[$v['name']] : $v['value'];
            $result[$k] = $v;
        }
        return $this->setFullConfig($name, $result);
    }

    public function getList()
    {
        return $this->infos;
    }

    public function refreshInfo()
    {
        $this->infos = $this->getInfos();
        ev('SyncData', __CLASS__, 'syncInfos');
        return $this;
    }

    public function refreshConfig()
    {
        $this->configs = $this->getConfigs();
        ev('SyncData', __CLASS__, 'syncConfigs');
        return $this;
    }

    protected function getInfos()
    {
        $infos = [];
        foreach (scandir(app_path()) as $dir) {
            if (in_array($dir, ['.', '..'])) continue;
            $info_file = app_path() . DS . $dir . DS . 'info.ini';
            if (!file_exists($info_file)) continue;
            $info = parse_ini_file($info_file);
            $info['status'] = intval($info['status']);
            $infos[$info['name']] = $info;
        }
        return $infos;
    }

    protected function getConfigs()
    {
        $configs = [];
        foreach (scandir(app_path()) as $dir) {
            if (in_array($dir, ['.', '..'])) continue;
            $file = app_path() . DS . $dir . DS . 'config.json';
            if (!file_exists($file)) continue;
            $configs[$dir] = (array)json_decode(file_get_contents($file), true);
        }
        return $configs;
    }

    public function syncInfos()
    {
        $this->infos = $this->getInfos();
    }

    public function syncConfigs()
    {
        $this->configs = $this->getConfigs();
    }

    public function refresh()
    {
        return $this->refreshInfo()->refreshConfig();
    }

    public static function enable($name)
    {
        $info = get_module_info($name);
        if (empty($info)) throw new Exception("Module is not exists");
        $info['status'] = 1;
        set_module_info($name, $info);
        $dir = app_path() . DS . $name . DS . 'public';
        if (is_dir($dir)) copy_files($dir, public_path());
        app(\yi\EventLib::class)->enable($name);
        self::refreshLang();
        $class = "\\app\\{$name}\\Plugin";
        if (class_exists($class)) $class::enable();
        event('RefreshLangVersion');
        return true;
    }

    
    public static function disable($name)
    {
        $info = get_module_info($name);
        if (empty($info)) throw new Exception(lang("Module is not exists"));
        $info['status'] = 0;
        set_module_info($name, $info);
        app(\yi\EventLib::class)->disable($name);
        self::refreshLang();
        $class = "\\app\\{$name}\\Plugin";
        if (class_exists($class)) $class::disable();
        event('RefreshLangVersion');
        return true;
    }

    public static function install($path, $force = false)
    {
        $result = [];
        Db::beginTransaction();
        $upgrade = false;
        try {
            $zip = new \ZipArchive();
            if ($zip->open($path) !== TRUE) throw new Exception(lang("Unable to open compressed package"));
            $info = parse_ini_string($zip->getFromName('info.ini'));
            if (empty($info['name'])) throw new Exception(lang('The file "info" parsing error'));
            $name = $info['name'];
            $modules = get_full_module_list();
            if (isset($modules[$name])) {
                if (!$force) {
                    throw new Exception("Module already exists!");
                }
                $upgrade = true;
                $config = get_module_full_config($name);
                mkfile(runtime_path() . DS . 'temp' . DS . 'module' . DS . $name . '.json', json_encode($config, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
                self::backup($name);
            }

            $module_path = app_path() . DS . $name . DS;
            $_tmp_path = runtime_path() . DS . 'temp' . DS . 'modules' . DS . $name . DS . time() . DS;
            $lock_file_path = $module_path . '.lock';
            if (is_file($lock_file_path)) {
                $list = file($lock_file_path);
                foreach ($list as $v) {
                    $_v = str_replace(['/', '\\'], [DS, DS], trim($v));
                    $_lock_file = $module_path . $_v;
                    $_new_lock_file = $_tmp_path . $_v;
                    if (is_file($_lock_file)) mkfile($_new_lock_file, file_get_contents($_lock_file));
                }
            }

            $zip->extractTo($module_path);
            if (is_dir($_tmp_path)) {
                copy_files($_tmp_path, $module_path);
                @rmdirs($_tmp_path, true);
            }
            static::installSql($name, $info);
            self::copyTheme($name);
            if (!empty($upgrade)) {
                self::mergeConfig($name, $config);
            }
            static::moduleInstall($name);
            Db::commit();
        } catch (\ZipException $e) {
            if (!empty($upgrade)) self::backup($name);
            @unlink($module_path);
            refresh_modules();
            throw new Exception($e->getMessage());
        } catch (\Throwable $e) {
            Db::rollBack();
            refresh_modules();
            throw new Exception($e->getMessage());
        }
        return $name;
    }

    public static function uninstall($name)
    {
        $info = get_module_info($name);
        if (empty($info)) throw new Exception(lang('Module is not exists'));
        if ($info['status'] != 0) throw new Exception(lang("Please disable the module first"));
        $class = "\\app\\{$name}\\Plugin";
        if (class_exists($class)) $class::uninstall();
        $dirs = ['public/modules', 'public/static/modules'];
        foreach ($dirs as $dir) {
            $d = base_path() . DS . $dir . DS . $name;
            if (is_dir($d)) rmdirs($d, true);
        }
        @rmdirs(app_path() . DS . $name);
        self::removeThemes($name);
        app(\yi\EventLib::class)->uninstall($name);
        event('RefreshLangVersion');
        return true;
    }

    public static function installSql($name, $info)
    {
        $result = run_command('module', "--name $name --action sql");
        static::checkCommandResult($result);
    }

    public static function copyTheme($name)
    {
        $result = run_command('module', "--name $name --action theme");
        static::checkCommandResult($result);
    }
    
    public static function moduleInstall($name)
    {
        $result = run_command('module', "--name $name --action install");
        static::checkCommandResult($result);
    }

    public static function mergeConfig($name)
    {
        $result = run_command('module', "--name $name --action mergeconfig");
        static::checkCommandResult($result);
    }
    
    public static function removeThemes($name)
    {
        $themes = array_diff(scandir(base_path() . DS . 'view'), ['.', '..']);
        foreach ($themes as $theme) {
            $dir = base_path() . DS . 'view' . DS . $theme . DS . $name . DS;
            if (is_dir($dir)) rmdirs($dir);
        }
    }

    public static function backup($name)
    {
        run_command('module', "--name $name --action package --backup 1");
    }

    public static function checkCommandResult($data)
    {
        $res = explode('\\n', trim($data));
        $result = $res[count($res) - 1];
        if ($res[count($res) - 1] != 'success') {
            throw new Exception($data);
        }
    }

    public static function refreshLang()
    {
        $langs = [];
        $list = get_full_module_list();
        foreach ($list as $info) {
            if (!$info['status']) continue;
            $lang_path = app_path() . DS . $info['name'] . DS . 'lang';
            if (!is_dir($lang_path)) continue;
            foreach (scandir($lang_path) as $lang) {
                $lang_file = $lang_path . DS . $lang;
                if (!is_file($lang_file)) continue;
                $data = include $lang_file ?: [];
                if (isset($langs[$lang])) $langs[$lang] = array_merge($langs[$lang], $data);
                else $langs[$lang] = $data;
            }
        }
        foreach ($langs as $lang => $data) {
            if (!Str::endsWith($lang, '.php')) continue;
            mkfile(base_path() . DS . 'translations' . DS . $lang, "<?php \n\nreturn " . var_export($data, true) . ';');
        }
    }
}