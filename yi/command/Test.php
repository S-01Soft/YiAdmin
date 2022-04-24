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

class Test extends Command
{
    protected static $defaultName = 'test';
    protected static $defaultDescription = '';

    protected function configure()
    {
        $this->addOption('name', 'name', InputOption::VALUE_REQUIRED, 'name');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return self::SUCCESS;
    }

}