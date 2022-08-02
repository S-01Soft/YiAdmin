<?php

namespace app\system\controller\admin;

use support\Db;
use yi\Auth;
use yi\Tree;
use yi\Menu;
use app\system\model\admin\AuthRuleModel;

/**
 * @Menu(title=Dashbord,weigh=99999,parent=0,ismenu=1,icon=fa fa-sitemap,ignore=index|add|edit|tree|delete|tree_list|tree_array)
 */
class Index extends Base
{
    public $noNeedLogin = ['login', 'logout', 'lang'];
    public $noNeedCheck = ['index', 'dashbord', 'changeLang', 'lang', 'status'];

    public function before()
    {
        $this->logic = \app\system\logic\admin\IndexLogic::instance(true);
        parent::before();
    }

    /**
     * @Menu(title=Dashbord,ismenu=1)
     */
    public function dashbord()
    {
        if ($this->request->isAjax()) {
            try {
                $form = $this->request->post('form');
                $result = $this->logic->getStatisticsInfo($form);
                return $this->success($result);
            } catch (Exception $e) {
                return $this->error($e->getMessage(), $e->getCode());
            }
        }
        $data = [];
        $data['phpversion'] = @phpversion();
        $data['os']         = PHP_OS;
        $data['uname'] = php_uname();
        $res = \support\Db::select("select VERSION() as version");
        $data['mysql'] = $res[0]->version;
        if (ini_get('file_uploads')) $data['upload_size'] = ini_get('upload_max_filesize');
        else $data['upload_size'] = 'Upload Disabled';
        $data['memory'] = format_bytes(memory_get_usage());

        $this->assign('data', $data);
        return $this->fetch();
    }

    public function index()
    {
        $dashbord = get_module_group_config('system', 'base')['dashbord'] ?: '/system/admin/index/dashbord';
        if (request()->isAjax()) {
            $query = AuthRuleModel::where('status', 1);
            $auth = get_admin();
            $ids = [];
            if ($auth->id != 1) {
                $groups = \app\system\logic\admin\AuthGroupLogic::instance()->getGroups();
                foreach ($groups as $v) {
                    $ids = array_merge($ids, explode(',', $v['rules']));
                }
                $query->whereIn('id', $ids);
            }
            $list = $query->where('ismenu', 1)->orderBy('weigh', 'DESC')->get()->toArray();
            $tree = Tree::instance();
            $tree->init($list);
            $list = $tree->getTreeArray(0);
            // $dashbord = get_module_group_config('system', 'base')['dashbord'] ?: '/system/admin/index/dashbord';
            if (!empty($list[0]) && $list[0]['name'] == '/system/admin/index') {
                $item = $list[0];
                $item['name'] = $dashbord;
                $item['childlist'] = [];
                $list[0] = $item;
            }
            return $this->success($list);
        }
        $this->assign('dashbord', $dashbord);
        return $this->fetch();
    }
    

    public function login()
    {
        $admin = get_admin();
        if ($admin->isLogin()) return redirect('/system');
        if (request()->isAjax()) {
            try {
                $form = request()->post('form');
                validate(\app\system\validate\admin\AdminValidate::class)->scene('login')->check($form);
                $data = $this->logic->login($form);
                return $this->success($data);
            } catch (ValidateException $e) {
                return $this->error($e->getMessage());
            } catch (Exception $e) {
                return $this->error($e->getMessage(), $e->getCode());
            }
        }
        return $this->fetch();
    }

    public function logout()
    {
        try {
            get_admin()->logout();
            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getApps()
    {
        $list = get_full_module_list();
        $result = [];
        foreach ($list as $name => $info) {
            $info['title'] = lang($info['title']);
            $result[] = $info;
        }
        return $this->success($result);
    }

    public function lang()
    {
        $module = request()->get('m');
        $controller = request()->get('c');
        $modulePath = app_path() . DS . $module . DS;
        
        $lang = request()->var('lang');
        
        $dirs = [
            base_path() . DS . 'translations' . DS,
            $modulePath . 'lang' . DS . snake_controller($controller, DS) . DS,
        ];
        $lang->loadDirs($dirs);
        $offset = config('app.debug') ? 0 : 30 * 60 * 60 * 24;
        $response = jsonp(json_encode($lang->get(), JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE), 'define');
        $response->withHeaders([
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public',
            'Pragma' => 'cache',
            'Expires' => gmdate("D, d M Y H:i:s", time() + $offset) . " GMT"
        ]);
        return $response;
    }

    public function getThemes()
    {
        if (!is_dir(view_path())) return $this->success([]);
        $dirs = scandir(view_path());
        $list = [];
        foreach ($dirs as $name) {
            if (!in_array($name, ['.', '..']) && is_dir(view_path() . DS . $name)) {
                $list[] = ['title' => $name, 'name' => $name];
            }
        }
        return $this->success($list);
    }
    
    /**
     * @Menu(title=Refresh Menu)
     */
    public function refreshMenu()
    {
        $form = $this->request->post('form');
        $name = $form['name'];
        try {
            if (empty($name)) {
                $modules = get_full_module_list();
                $out = [];
                foreach ($modules as $k => $v) {
                    $out[] = $this->refreshAppMenu($v['name'], 'admin', 'admin');
                    $out[] = $this->refreshAppMenu($v['name'], 'user', 'index,api');
                }
            } else {
                $out[] = $this->refreshAppMenu($name, 'admin', 'admin');
                
                $out[] = $this->refreshAppMenu($name, 'user', 'index,api');
            }
            return $this->success($out);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function status()
    {
        return $this->success(get_system_status());
    }

    /**
     * @Menu(title=Restart System)
     */
    public function restart()
    {
        system_reload();
        return $this->success();
    }
    
    protected function refreshAppMenu($name, $scene, $dir)
    {
        return Menu::install($name, $scene, $dir);
    }

    public function changeLang()
    {
        $lang = request()->get('lang');
        event('RefreshLangVersion');
        return $this->success(null, null, function($response) use ($lang) {
            $response->cookie('lang', $lang, 0, '/');
        });
    }

    /**
     * @Menu(title=App Upgrade)
     */
    public function upgrade()
    {
        if ($this->request->isAjax()) {
            if ($this->request->isGet()) {
                $version = get_version();
                $data = \yi\Http::get(config('app.api_url') . '/version/api/index/log', ['version' => $version,'token' => cache('SOFT_01_TOKEN' . get_admin()->id)]);
                if (!isset($data['code'])) return $this->error($data);
                if ($data['code'] == 1) return $this->success($data['data']);
                else return $this->error($data['message']);
            } else {
                $versions = $this->request->post('versions');
                $versions = version_sort($versions, 'asc');
                $system_full_config = get_module_full_config('system');
                foreach ($versions as $v) {
                    $dir = RUNTIME_PATH . 'system' . DS . 'upgrade-files' . DS . $v;
                    if (is_dir($dir)) {
                        copy_files($dir, BASE_PATH);
                        rmdirs($dir, true);
                    }
                }
                $new_system_full_config = (array)json_decode(file_get_contents(APP_PATH . DS . 'system' . DS . 'config.json'), true);
                $config = [];
                foreach ($system_full_config as $key => $item) {
                    if (isset($new_system_full_config[$key])) $item['value'] = $new_system_full_config[$key]['value'];
                    $config[$key] = $item;
                }
                foreach ($new_system_full_config as $key => $item) {
                    if (!isset($config[$key])) $config[$key] = $item;
                }
                set_module_full_config('system', $config);
                \yi\module::refreshLang();
                event('RefreshLangVersion');
                return $this->success($versions);
            }
        }
        return $this->fetch();
    }

    /**
     * @Menu(title=Get System UpgradeFiles)
     */
    public function get_upgrade_files() 
    {
        $version = request()->post('version');
        $file = request()->post('file');
        $data = $this->logic->get_upgrade_files($version, $file);
        return $this->success($data);
    }

    /**
     * @Menu(title=Files Diff)
     */
    public function diff()
    {
        $file = request()->get('file');
        $version = request()->get('version');
        $new_file = RUNTIME_PATH . 'system' . DS . 'upgrade-files' . DS . $version . DS . $file;
        $old_file = BASE_PATH . DS . $file;
        if (!file_exists($new_file)) throw new Exception(lang('File not exists'));
        $new = file_get_contents($new_file);
        if (!file_exists($old_file)) $old = '';
        else $old = file_get_contents($old_file);
        $this->assign('new_file', $new);
        $this->assign('old_file', $old);
        return $this->fetch();
    }
}
