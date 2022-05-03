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
use yi\exception\ModuleDisabledException;

class Module extends Command
{
    protected static $defaultName = 'module';
    protected static $defaultDescription = 'create|package|move|install|enable|disable module.';

    protected function configure()
    {
        $this->addOption('name', 'name', InputOption::VALUE_REQUIRED, 'name')
            ->addOption('action', 'action', InputOption::VALUE_OPTIONAL, 'create|move|package, default create')
            ->addOption('title', 'title', InputOption::VALUE_OPTIONAL, 'module title')
            ->addOption('desc', 'desc', InputOption::VALUE_OPTIONAL, 'module description')
            ->addOption('author', 'author', InputOption::VALUE_OPTIONAL, 'module author')
            ->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'force override')
            ->addOption('env', 'env', InputOption::VALUE_OPTIONAL, '')
            ->addOption('backup', 'backup', InputOption::VALUE_OPTIONAL, '');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $action = $input->getOption('action') ?: 'create';
        $name = $input->getOption('name');
        $title = $input->getOption('title') ?: '插件名称';
        $desc = $input->getOption('desc') ?: '插件描述';
        $author = $input->getOption('author') ?: 'author';
        $force = $input->getOption('force') ?: false;
        $backup = $input->getOption('backup') ?: false;
        $env = $input->getOption('env') ?: 'pro';
        try {
            switch ($action) {
                case 'create':
                    if (in_array($name, array_column(get_full_module_list(), 'name'))) {
                        $output->writeln("Module already exsists");
                    } else {
                        $this->create($name, $title, $desc, $author);
                        $output->writeln("Module [$name] create successful");
                    }
                    break;
                case 'package':
                    $module_dir = app_path() . DS . $name . DS;
                    $info = get_module_info($name);
                    $module_tmp_path = runtime_path() . DS . 'module' . DS;
                    if (!is_dir($module_tmp_path)) {
                        @mkdir($module_tmp_path, 0755, true);
                    }
                    $module = $module_tmp_path . $name . '-' . $info['version'] . ($backup ? '-' . time() : '') . '.zip';
                    
                    $zip = new \ZipArchive;
                    $zip->open($module, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                    $cache_files = [];
                    scan_dir($module_dir, function($it, $iterator) use (&$zip, $module_dir, $env, &$cache_files) {
                        if (!$it->isDir()) {
                            $file = $it->getRealPath();
                            if (in_array($file, $cache_files)) return;
                            $ignore = is_file($module_dir . '.ignore') ? explode(' ', preg_replace("/\s+/", " ", trim(file_get_contents($module_dir . '.ignore')))) : [];
                            
                            foreach ($ignore as $v) {
                                $pattern = "/" . str_replace(['\\', '/'], ['\\\\', '\/'], "^(" . $module_dir . ")") . $v . "/";
    
                                preg_match($pattern, $file, $res);
    
                                if (!empty($res)) return;
                            }
                            $relativePath = str_replace(DS, '/', substr($file, strlen($module_dir)));
                            if (!empty($env) && Str::endsWith($it->getFilename(), '.' . $env)) {
                                $relativePath = substr($relativePath, 0, strlen($relativePath) - strlen('.' . $env));
                                $cache_files[] = $file;
                            }
    
                            $zip->addFile($file, $relativePath);
                        }
                    });
                    $zip->close();
                    $output->writeln("Module [$name] Package Successful!");
                    break;
                case 'move':
                    $this->move($name, $force);
                    $output->writeln("Module [$name] Move Successful");
                    break;
                case 'install':
                    $this->install($name);
                    break;
                case 'enable':
                    $this->enable($name);
                    break;
                case 'disable':
                    $this->disable($name);
                    break;
                case 'sql':
                    $this->importSql($name);
                    break;
                case 'theme':
                    $this->copyTheme($name);
                    break;
                case 'mergeconfig':
                    $this->mergeConfig($name);
                    break;
            }
            $output->writeln('success');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage());
            return self::FAILURE;
        }
    }

    private function create($name, $title, $desc, $author)
    {
        $source_dir = base_path() . DS . 'yi' . DS . 'command' . DS . 'module' . DS . 'tpl' . DS;
        $dest_dir = app_path() . DS . $name . DS;
        $ext_array = [
            'tpl' => 'php'
        ];
        scan_dir($source_dir, function($it, $iterator) use ($source_dir, $dest_dir, $name, $ext_array, $title, $desc, $author) {
            $source = $source_dir . $iterator->getSubPathName();
            $dest = $dest_dir . $iterator->getSubPathName();
            if ($it->isDir()) mkdir($dest, 0755, true);
            else {
                $content = str_replace(['%name%', '%title%', '%desc%', '%author%'], [$name, $title, $desc, $author], file_get_contents($source));
                $ext = $it->getExtension();
                if (isset($ext_array[$ext])) {
                    $dest = substr($dest, 0, strlen($dest) - strlen($ext)) . $ext_array[$ext];
                }
                mkfile($dest, $content);
            }
        });
    }

    private function move($name, $force)
    {
        $movePath = [
            'publicDir' => ['public/modules', 'public/static/modules'],
            'themeDir' => 'template'
        ];
        $paths = [];
        $appPath = str_replace('/', DS, app_path()) . DS;
        $rootPath = str_replace('/', DS, base_path()) . DS;
        foreach ($movePath as $k => $items) {
            switch ($k) {
                case 'publicDir':
                    $module_dir = app_path() . DS . $name . DS;
                    $ignore = is_file($module_dir . '.packignore') ? explode(' ', preg_replace("/\s+/", " ", trim(file_get_contents($module_dir . '.packignore')))) : [];
                    foreach ($items as $v) {
                        $v = str_replace('/', DS, $v);
                        $oldPath = $rootPath . $v . DS . $name;
                        if (!is_dir($oldPath)) continue;
                        scan_dir($oldPath, function($it, $iterator) use ($name, $ignore) {
                            if (!$it->isDir()) {
                                $file = $it->getRealPath();
                                foreach ($ignore as $v) {
                                    $pattern = "/" . str_replace(['\\', '/'], ['\\\\', '\/'], "^(" . base_path() . DS . ")") . $v . "/";
                                    preg_match($pattern, $file, $res);
                                    if (!empty($res)) return;
                                }
                                $newPath = app_path() . DS . $name . DS . substr($file, strlen(base_path() . DS));
                                mkfile($newPath);
                                copy($file, $newPath);
                            }
                        });
                    }
                break;
                case 'themeDir':
                    $themepath = view_path() . DS . 'default' . DS;
                    if (!is_dir($themepath)) break;
                    $themes = scandir($themepath);
                    if (is_dir($themepath . $name)) copy_files($themepath . $name . DS, app_path() . DS . $name . DS . $items . DS . 'default' . DS);
                break;
            }
        }
    }
    
    private function importSql($name)
    {
        refresh_modules();
        $info = get_module_info($name);
        $base_path = app_path() . DS . $name . DS . 'install' . DS;
        if (!is_dir($base_path)) return;
        $history = Db::table('upgrades')->where('app', $name)->pluck('name')->toArray();
        foreach (scandir($base_path) as $name) {
            if (in_array($name, ['.', '..'])) continue;
            if (!Str::endsWith($name, '.sql')) continue;
            $list[] = substr($name, 0, strlen($name) - 4);
        }
        $list = version_sort($list);
        foreach ($list as $fname) {
            $filename = $fname . '.sql';
            $file = $base_path . $fname . '.sql';
            if (file_exists($file) && !in_array($filename, $history)) {
                $sqls = split_sql($file);
                foreach ($sqls as $sql) {
                    Db::statement($sql);
                }

                $data = [
                    'app' => $name,
                    'name' => $filename,
                    'version' => $info['version'],
                    'created_at' => time()
                ];
                Db::table('upgrades')->insert($data);
            }
        }
    }

    private function copyTheme($name)
    {
        $module_path = app_path() . DS . $name . DS;
        if (is_dir($module_path . 'template')) {
            $themes = array_diff(scandir($module_path . 'template'), ['.', '..']);
            foreach ($themes as $theme) {
                copy_files($module_path . 'template' . DS . $theme . DS, base_path() . DS . 'view' . DS . $theme . DS . $name . DS);
            }
        }
    }
    
    private function mergeConfig($name)
    {
        $result = [];
        $data = get_module_full_config($name);
        $file = runtime_path() . DS . 'temp' . DS . 'module' . DS . $name . '.json';
        if (file_exists($file)) $config = (array)json_decode(file_get_contents($file), true);
        else $config = [];
        foreach ($data as $key => $item) {
            if (isset($config[$key])) $item['value'] = $config[$key]['value'];
            $result[$key] = $item;
        }
        foreach ($config as $key => $item) {
            if (!isset($result[$key])) $result[$key] = $item;
        }
        if (file_exists($file)) @unlink($file);
        set_module_full_config($name, $result);
    }

    private function install($name)
    {
        $class = "\\app\\{$name}\\Plugin";
        if (class_exists($class)) $class::install();
    }

}