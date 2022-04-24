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

class Install extends Command
{
    protected static $defaultName = 'install';
    protected static $defaultDescription = 'Install system';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addOption('name', 'u', InputOption::VALUE_OPTIONAL, 'admin name')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'admin password, default 123456');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (is_installed()) {
            $output->writeln('System is installed!');
            return self::FAILURE;
        }
        $this->output = $output;
        $username = $input->getOption('name') ?: 'admin';
        $password = $input->getOption('password') ?: '123456';
        $config = get_db_config();
        $config['database'] = 'performance_schema';
        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($config, 'init');
        $capsule->addConnection(get_db_config(), 'default');
        $capsule->setAsGlobal();
        $this->createDatabase();
        $this->execSql();
        Db::beginTransaction();
        try {
            $this->createAdmin($username, $password);
            $this->loadEvent();
            $this->importMenu();
            mkfile(runtime_path() . DS . 'install.lock', date('Y-m-d H:i:s'));
            Db::commit();
            return self::SUCCESS;
        } catch (\PDOException $e) {
            $output->writeln($e->getMessage());
            Db::rollBack();
        } catch (Exception $e) {
            $output->writeln($e->getMessage());
            Db::rollBack();
        }
        return self::FAILURE;
    }

    protected function createDatabase()
    {
        $config = get_db_config();
        $sql = "CREATE DATABASE IF NOT EXISTS `" . $config['database'] . "` DEFAULT CHARACTER SET " . $config['charset'];
        Db::connection('init')->statement($sql);
    }

    protected function execSql()
    {
        $base_path = app_path() . DS . 'system' . DS . 'data' . DS;
        foreach (scandir($base_path) as $filename) {
            if (in_array($filename, ['.', '..'])) continue;
            $sqls = split_sql($base_path . $filename);
            foreach ($sqls as $sql) {
                Db::select($sql);
                list($type, $name) = $this->parseSqlInfo($sql);
                switch($type) {
                    case 'CREATE':
                        $this->output->writeln("创建数据表【 $name 】成功。");
                    break;
                    case 'INSERT':
                        $this->output->writeln("导入【 $name 】数据成功。");
                    break;
                    default:
                    break;
                }
            }
        }
    }

    protected function createAdmin($name, $password)
    {
        $admin = new AdminModel;
        $admin->username = $name;
        $admin->password = $password;
        $admin->save();
        $this->output->writeln("创建管理员成功。");
    }

    protected function importMenu()
    {
        run_command('menu', '--name system --scene admin --dir admin');
        $this->output->writeln("导入菜单成功。");
    }

    protected function loadEvent()
    {
        \app\system\logic\admin\EventLogic::instance()->refreshEvent();
        $this->output->writeln("导入事件成功。");
    }

    protected function parseSqlInfo($sql)
    {
        $reg = "/CREATE.+?`.+?`/";
        preg_match($reg, $sql, $res);
        $type = "";
        if (!empty($res)) {
            $type = 'CREATE';
            $sql = $res[0];
        } else {
            $reg = "/INSERT.+?`.+?`/";
            preg_match($reg, $sql, $res);
            if (!empty($res)) {
                $type = 'INSERT';
                $sql = $res[0];
            }
        }
        
        preg_match("/(?<=`).+(?=`)/", $sql, $name);
        return [$type, $name[0] ?? ''];
    }

}
