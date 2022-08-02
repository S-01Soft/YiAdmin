<?php

namespace yi\controller;

use support\Db;
use support\Str;
use support\exception\Exception;
use yi\exception\ValidateException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

abstract class AdminController extends BaseController 
{
    public $middlewares = [
        \yi\middlewares\AdminAuthCheck::class,
    ];
    
    public $needLogin = ['*'];

    public $noNeedLogin = [];

    public $noNeedCheck = [];

    protected $has_tree = true;

    protected $pid_name = 'parent_id';

    protected $id_name = 'id';

    protected $field_name = 'name';

    public $hidden = [];

    protected function fetch(string $template = '', array $vars = [])
    {
        $template = empty($template) ? (app_path() . DS . request()->getModule() . DS . 'view' . DS . snake_controller(request()->getController()) . DS . request()->getAction() . '.html') : $template;
        return view($template, $vars);
    }

    public function before()
    {
        parent::before();
        $payload = (object)[
            'controller' => $this
        ];
        event('BeforeAdminController', $payload);
        app(\yi\Event::class)->bind('BeforeRender', 'yi\\events\\Event@onSetAdminRenderOption');
        $this->config('module', request()->getModule());
        $this->config('controller', str_replace('\\', '/', request()->getController()));
        $this->config('action', request()->getAction());
        $this->config('statics', get_module_group_config('system', 'statics'));
        $this->config('admin', array_merge_deep(request()->config('common'), request()->config('admin')));
        $this->config('langVersion', ev('GetLangVersion'));
        $this->config('version', get_version());
        $this->config('lang', request()->var('locale'));
        $this->config('debug', config('app.debug'));
        $this->assign('admin', get_admin());
        $this->assign('auth', get_admin());
        \yi\Widget::group('admin');
        if (request()->getModule() != 'system') $this->config('moduleVersion', get_module_info(request()->getModule())['version']);
        $this->assignconfig();
        $this->loadlang();
    }

    public function after()
    {
        parent::after();
        $payload = (object)[
            'controller' => $this
        ];
        event('AfterAdminController', $payload);
    }

    /**
     * @Menu(title=View,ismenu=1)
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            try {
                $data = $this->logic->paginate();
                return $this->success($data);
            } catch (Exception $e) {
                return $this->error($e->getMessage());
            }
        }
        $this->logic->paginateView($this);
        $this->assign('hidden', $this->hidden);
        return $this->fetch();
    }


    /**
     * @Menu(title=All Records)
     */
    public function all()
    {
        try {
            $data = $this->logic->all();
            return $this->success($this->logic->all());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Select)
     */
    public function select()
    {
        if ($this->request->isAjax()) {
            try {
                $data = $this->logic->select();
                return $this->success($data);
            } catch (Exception $e) {
                return $this->error($e->getMessage(), $e->getCode());
            }
        }
        $this->logic->selectView($this);
        $this->assign('hidden', $this->hidden);
        return $this->fetch();
    }

    /**
     * @Menu(title=Add)
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $form = $this->request->post('form');
            try {
                validate($this->validate_cls)->scene('add')->check($form);
                $row = $this->logic->postAdd($form);
                return $this->success($row);
            } catch (Exception $e) {
                return $this->error($e->getMessage(), $e->getCode());
            } catch (ValidateException $e) {
                return $this->error($e->getMessage());
            }
        }
        $this->logic->addView($this);
        $this->assign('hidden', $this->hidden);
        return $this->fetch();
    }

    /**
     * @Menu(title=Edit)
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $form = $this->request->post('form');
            try {
                validate($this->validate_cls)->scene('edit')->check($form);
                $row = $this->logic->postEdit($form);
                return $this->success($row);
            } catch (Exception $e) {
                return $this->error($e->getMessage(), $e->getCode());
            } catch (ValidateException $e) {
                return $this->error($e->getMessage());
            }
        }
        if ($this->request->isAjax()) {
            try {
                $id = $this->request->get($this->logic->model->getKeyName());
                $row = $this->logic->getEdit($id);
                return $this->success($row);
            } catch (Exception $e) {
                return $this->error($e->getMessage());
            }
        }
        $this->logic->editView($this);
        $this->assign('hidden', $this->hidden);
        return $this->fetch();
    }

    /**
     * @Menu(title=Toggle)
     */
    public function toggle()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $id = $this->request->get($this->logic->model->getKeyName());
            try {
                $data = $this->logic->toggle($id, $params);
                return $this->success($data);
            } catch(Exception $e) {
                return $this->error($e->getMessage());
            }
        }
    }


    /**
     * @Menu(title=Delete)
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $ids = $this->request->post('ids');
            try {
                $this->logic->delete();
                return $this->success();
            } catch(Exception $e) {
                return $this->error($e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * @Menu(title=Export) 
    */
    public function exports()
    {
        try {
            $params = request()->post();
            $fields = empty($params['fields']) ? [] : (array)json_decode(htmlspecialchars_decode($params['fields']), true);
            if (empty($fields)) throw new Exception(lang("No fields selected for export"));
            $params['fields'] = $fields;
            $rows = $this->logic->export();
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            if (!empty($rows)) {
                $i = 0;
                foreach ($rows[0] as $k => $v) {
                    $sheet->setCellValueExplicitByColumnAndRow($i + 1, 1, $k, 's');
                    $i ++;
                }
            }
            foreach ($fields as $i => $v) {
                $sheet->setCellValueExplicitByColumnAndRow($i + 1, 2, $v['title'], 's');
            }
            foreach ($rows as $i => $row) {
                $j = 0;
                foreach ($row as $key => $val) {
                    $sheet->setCellValueExplicitByColumnAndRow($j + 1, $i + 3, $val, 's');
                    $j ++;
                }
            }
            $sheet->getRowDimension('1')->setRowHeight(0);
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            unset($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_contents();
            ob_clean();
            
            $filename = empty($params['title']) ? lang("Unnamed") : $params['title'];
            return response($content)->withHeaders([
                'Pragma' => 'public',
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment;filename="'.$filename.'.xlsx"',
                'Cache-Control' => 'max-age=0'
            ]);
        } catch(Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Import)
     */
    public function imports()
    {
        try {
            $data = $this->logic->import();
            return $this->success($data);
        } catch(Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Tree Array)
     */
    public function tree_array()
    {
        if (!$this->has_tree) return $this->error(lang("The data is empty"));
        try {
            $list = $this->logic->tree_array($this->pid_name, $this->id_name, 0);
            return $this->success($list);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Menu(title=Tree List)
     */
    public function tree_list()
    {
        if (!$this->has_tree) return $this->error(lang("The data is empty"));
        try {
            $list = $this->logic->tree_list($this->field_name, $this->pid_name, $this->id_name, 0);
            if (request()->get('type') != 1) array_unshift($list, ['id' =>0, $this->field_name => lang('None')]);
            return $this->success($list);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}