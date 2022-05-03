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
use support\exception\Exception;
use yi\Annotation;
use yi\Tree;
use app\system\model\admin\AdminModel;
use app\system\model\admin\UserModel;

class Up extends Command
{
    protected static $defaultName = 'up';
    protected static $defaultDescription = 'Upgrade system';

    protected function configure()
    {
        $this->addOption('action', 'a', InputOption::VALUE_OPTIONAL, 'action');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getOption('action', '');
        $actions = explode(':', $action);
        $base_path = app_path() . DS . 'system' . DS . 'data' . DS;
        if (!is_dir($base_path)) return;
        $history = Db::table('upgrades')->where('app', 'system')->pluck('name')->toArray();
        $list = [];
        foreach (scandir($base_path) as $name) {
            if (in_array($name, ['.', '..'])) continue;
            if (in_array($name, $history)) continue;
            if (!Str::endsWith($name, '.sql')) continue;
            $list[] = substr($name, 0, strlen($name) - 4);
        }
        $list = version_sort($list);
        $version = get_version();
        foreach ($list as $fname) {
            $filename = $fname . '.sql';
            $file = $base_path . $fname . '.sql';
            if (file_exists($file)) {
                $sqls = split_sql($file);
                
                foreach ($sqls as $sql) {
                    Db::statement($sql);
                }

                $data = [
                    'app' => 'system',
                    'name' => $filename,
                    'version' => $version,
                    'created_at' => time()
                ];
                Db::table('upgrades')->insert($data);
            }
        }
        foreach ($actions as $action) {
            switch($action) {
                case 'uid':
                    $user = \yi\User::instance();
                    UserModel::whereNull('uid')->chunk(100, function($rows) use ($user) {
                        foreach ($rows as $row) {
                            Db::table('user')->where('id', $row->id)->update(['uid' => $user->createUid()]);
                        }
                    });
                    break;
            }
        }
        return self::SUCCESS;
    }

}
