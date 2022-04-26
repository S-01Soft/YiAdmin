<?php

namespace yi\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ClassLoader\ClassMapGenerator;
use ReflectionClass;
use ReflectionMethod;
use support\Str;
use support\Db;
use yi\Annotation;
use yi\Tree;

class Menu extends Command
{
    protected static $defaultName = 'menu';
    protected static $defaultDescription = 'create/refresh menu.';

    protected $dbName = 'auth_rule';
    protected $option = [];
    /**
     * @return void
     */
    protected function configure()
    {
        $this->addOption('name', 'name', InputOption::VALUE_OPTIONAL, 'name')
            ->addOption('scene', 'scene', InputOption::VALUE_OPTIONAL, 'scene, default admin, admin|user')
            ->addOption('dir', 'dir', InputOption::VALUE_OPTIONAL, 'default admin, admin|index|api...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $dir = trim($input->getOption('dir')) ?: 'admin';
        $scene = $input->getOption('scene') ?: 'admin';
        $this->dbName = config("auth.$scene")['auth_rule'];
        $this->app_name = $app_name = trim($input->getOption('name')) ?: 'system';
        $this->controller_path = app_path() . DS . $app_name . DS . 'controller' . DS;
        $data = [];
        
        $info = get_module_info($app_name);
        $root_menu = [
            'app' => $app_name,
            'title' => $info['title'],
            'name' => $app_name,
            'ismenu' => 1,
            'auth' => 0,
            'parent' => $info['parent'] ?? 0,
            'icon' => $info['icon'] ?? 'fa fa-list',
            'weigh' => $info['sort'] ?? 99999,
            'type' => 1,
            'app_type' => 0,
            'status' => 1
        ];
        $data[] = $root_menu;
        $this->option['root_menu'] = $root_menu;
        $this->option['parser'] = new Annotation(['Menu']);
        $dirs = explode(',', $dir);
        foreach ($dirs as $d) {
            $this->getMenuData($data, $d);
        }
        $tree = Tree::instance()->init($data, 'name', 'parent');
        if (empty($data)) {
            $output->writeln("[$app_name]Menu is empty!");
            return;
        }

        try {
            $this->save($data);
        } catch (\PDOException $e) {
            $output->wirteln($e->getMessage());
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage());
        }
        return self::SUCCESS;
    }

    
    protected function getMenuData(&$data, $dir)
    {
        if (!is_dir($this->controller_path . $dir)) return;
        $app_name = $this->app_name;
        foreach (ClassMapGenerator::createMap($this->controller_path . $dir) as $class => $path) {

            $subDir = mb_substr($path, mb_strlen($this->controller_path), mb_strlen($path) - mb_strlen($this->controller_path) - 4);

            $refClass = new ReflectionClass($class);
            $methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC);
            $params = $this->option['parser']->parseClassComment($refClass);
            $ignore_menus = [];
            $class_name = $this->getClassName($subDir, $app_name);
            foreach ($params['Menu'] as $menu) {
                $ignore_menus = array_merge($ignore_menus, empty($menu['ignore']) ? [] : explode('|', $menu['ignore'])); // 忽略的菜单
                $class_data = [
                    'app' => $app_name,
                    'title' => $menu['title'] ?? '',
                    'name' => $menu['name'] ?? $class_name,
                    'ismenu' => $menu['ismenu'] ?? 1,
                    'auth' => $menu['auth'] ?? 0,
                    'parent' => $menu['parent'] ?? $this->option['root_menu']['name'] ?? 0,
                    'icon' => $menu['icon'] ?? '',
                    'weigh' => $menu['weigh'] ?? 10000,
                    'type' => 1,
                    'app_type' => 0,
                    'condition' => $menu['condition'] ?? '',
                    'remark' => $menu['remark'] ?? '',
                    'status' => 1
                ];
                if (isset($menu['off'])) {
                    $class_data = [];
                    continue;
                }
                $data[] = $class_data;
            }
            
            if (!$refClass->isAbstract() && !empty($class_data)) {
                foreach ($methods as $method) {
                    if (in_array($method->name, $ignore_menus) || in_array('*', $ignore_menus)) continue;
                    $params = $this->option['parser']->parseMethodComment($method);
                    if (!empty($params['Menu'])) {
                        foreach ($params['Menu'] as $menu) {
                            $data[] = [
                                'app' => $app_name,
                                'title' => $menu['title'] ?? '',
                                'name' => $menu['fullname'] ?? ($class_name . '/' . ($menu['name'] ?? $method->name)) ,
                                'ismenu' => empty($menu['ismenu']) ? 0 : 1,
                                'auth' => $menu['auth'] ?? 0,
                                'parent' => $menu['parent'] ?? $class_data['name'],
                                'icon' => $menu['icon'] ?? '',
                                'weigh' => $menu['weigh'] ?? 10000,
                                'condition' => $menu['condition'] ?? '',
                                'remark' => $menu['remark'] ?? '',
                                'status' => 1,
                                'type' => 0,
                                'app_type' => 0
                            ];
                        }
                    }
                }
            }
            $class_data = [];
        }
    }

    
    protected function save($menus)
    {
        $rules = Db::table($this->dbName)->where('app', $this->app_name)->pluck('name')->toArray();
        $addRules = [];
        foreach ($menus as $item) {
            if (count($menus) == 1) continue;
            $addRules[] = $item['name'];
            $parent = Db::table($this->dbName)->where('name', '=', $item['parent'] . '')->first();
            $data = [
                'app' => $item['app'],
                'name' =>  $item['name'],
                'title' => $item['title'],
                'ismenu' => $item['ismenu'],
                'pid' => $parent->id ?? 0,
                'icon' => $item['icon'],
                'status' => $item['status'],
                'parent_rule' => $item['parent'],
                'weigh' => $item['weigh'],
                'type' => $item['type'] ?? 1,
                'app_type' => $item['app_type'],
                'created_at' => time()
            ];
            if (in_array($item['name'], $rules)) {
                unset($data['status']);
                Db::table($this->dbName)->where('app', $item['app'])->where('name', $item['name'])->update($data);
                $id = Db::table($this->dbName)->where('app', $item['app'])->where('name', $item['name'])->first()->id;
            } else {
                $id = Db::table($this->dbName)->insertGetId($data);
            }
            Db::table($this->dbName)->where('app', $item['app'])->where('parent_rule', $item['name'])->update(['pid' => $id]);
            if (!empty($item['childlist'])) $this->save($item['childlist']);
        }
        $diff = array_diff($rules, $addRules);
        Db::table($this->dbName)->whereIn('name', $diff)->delete();
        if (!empty($diff)) $this->output->writeln("Remove " . count($diff) . " Rules: " . implode(', ', $diff));
    }

    protected function getClassName($name, $app)
    {
        $name = str_replace(DS, '.', $name);
        $arr = ['/' . $app, preg_replace_callback("/(?<=\.)\S+/", function($matches) {
            return Str::snake($matches[0]);
        }, $name)];
        if (empty($arr)) return null;
        $module = $arr[0];
        $controller = str_replace('.', '/', $arr[1]);
        return $module . '/' . $controller;
    }

}
