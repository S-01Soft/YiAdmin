<?php
namespace app\system\controller\admin;

use support\exception\Exception;

/**
 * @Menu(title=Module,weigh=99500,parent=0,icon=fa fa-th-large,ignore=add|edit|delete|toggle|select|all|tree|tree_list|tree_list)
 */
class Module extends Base 
{
    public $noNeedCheck = ['tags', 'getUserInfo', 'login', 'logout'];
        
    public function before()
    {
        parent::before();
        $this->logic = \app\system\logic\admin\ModuleLogic::instance(true);
    }

    /**
     * @Menu(title=Show,ismenu=1)
     */
    public function index()
    {
        if (request()->isAjax()) {
            $type = request()->get('type', 1);
            $page = request()->get('page', 1);
            $where = request()->get('where', 1, ['']);
            $where = (array)json_decode($where) ?? [];
            try {
                $data = $this->logic->getModuleList($type, $page, $where);
                $list = $data['data'];
                $list = \support\Arr::sort($list, function($item1, $item2) {
                    $a = $item1['sort'] ?? 10000;
                    $b = $item2['sort'] ?? 10000;
                    return $a == $b ? 0 : (($a > $b) ? -1 : 1);
                });
                $data['data'] = $list;
                return $this->success($data);
            } catch (Exception $e) {
                return $this->error($e->getMessage(), $e->getCode());
            }
        }
        return $this->fetch();
    }

    public function tags()
    {
        try {
            $data = $this->logic->getTags();
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 9999);
        }
    }

    /**
     * @Menu(title=Config)
     */
    public function option()
    {
        if ($this->request->isAjax()) {
            if ($this->request->isPost()) {
                $form = $this->request->post('form', [], []);
                $name = $this->request->post('name');
                $payload = (object) [
                    'name' => $name,
                    'form' => $form
                ];
                event(request()->getName() . 'BeforeSaveModuleOption', $payload);
                set_module_config($payload->name, $payload->form);
                event(request()->getName() . 'AfternSaveModuleOption', $payload);
                return $this->success();
            }
            if ($this->request->isGet()) {
                $name = $this->request->get('name');
                if ($group = $this->request->get('group')) {
                    $data = get_module_group_config($name, $group);
                    if ($var = $this->request->get('var')) return $this->success($data[$var]);
                    return $this->success($data);
                }
                $data = get_module_full_config($name);
                return $this->success($data);
            }
        }
        return $this->fetch();
    }

    /**
     * @Menu(title=Add Config)
     */
    public function add_option_item()
    {
        try {
            $this->request->filter([]);
            $name = request()->post('name');
            $form = request()->post('form');
            $data = $this->logic->addOptionItem($name, $form);
            return $this->success($data);
        } catch(Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Delete Config)
     */
    public function delete_option_item()
    {
        try {
            $name = request()->post('name');
            $option_name = request()->post('option_name');
            $data = $this->logic->deleteOptionItem($name, $option_name);
            return $this->success($data);
        } catch(Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Upload Module)
     */
    public function upload()
    {
        try {
            $data = $this->logic->upload();
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Install)
     */
    public function install()
    {
        if (!config('module.local')) return $this->error(lang('Local install is disabled'));
        try {
            $data = $this->logic->install();
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Uninstall)
     */
    public function uninstall()
    {
        if (!config('module.local')) return $this->error(lang('Cannot be uninstalled'));
        try {
            $name = request()->post('name');
            $this->logic->uninstall($name);
            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Run Stop)
     */
    public function setState()
    {
        try {
            $data = $this->logic->setState();
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Remote Install)
     */
    public function remoteInstall()
    {
        if (!config('module.remote')) return $this->error(lang('Remote install is disabled'));
        try {
            $form = request()->post();
            $data = $this->logic->remoteInstall($form);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Sort)
     */
    public function weigh()
    {
        try {
            $name = request()->post('name');
            $weigh = request()->post('weigh');
            $this->logic->weigh($name, $weigh);
            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Refresh Module)
     */
    public function refresh()
    {
        app(\yi\Module::class)->refresh();
        return $this->success();
    }

    public function getUserInfo()
    {
        try {
            $data = $this->logic->getUserInfo();
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function login()
    {
        try {
            $form = request()->post('form');
            $data = $this->logic->login($form);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function logout()
    {        
        try {
            $this->logic->logout();
            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}