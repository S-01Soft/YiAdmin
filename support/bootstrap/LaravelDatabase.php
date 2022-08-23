<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace support\bootstrap;

use Webman\Bootstrap;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Connection;
use Workerman\Worker;
use Workerman\Timer;
use support\Db;
use support\Cache;

/**
 * Class Laravel
 * @package support\bootstrap
 */
class LaravelDatabase implements Bootstrap
{
    /**
     * @param Worker $worker
     *
     * @return void
     */
    public static function start($worker)
    {
        if (!class_exists('\Illuminate\Database\Capsule\Manager')) {
            return;
        }

        $connections = config('database.connections');
        if (!$connections) {
            return;
        }

        $capsule = new Capsule;
        $configs = config('database');

        $capsule->getDatabaseManager()->extend('mongodb', function ($config, $name) {
            $config['name'] = $name;

            return new Connection($config);
        });

        if (isset($configs['default'])) {
            $default_config = $connections[$configs['default']];
            $capsule->addConnection($default_config);
        }

        foreach ($connections as $name => $config) {
            $capsule->addConnection($config, $name);
        }

        if (class_exists('\Illuminate\Events\Dispatcher')) {
            $capsule->setEventDispatcher(new Dispatcher(new Container));
        }

        $capsule->setAsGlobal();

        $capsule->bootEloquent();
        QueryBuilder::macro('getColumns', function() {
            $_tableName = $this->from;
            if (stripos($_tableName, 'as') !== false){
                $_tableName = trim(explode('as', $_tableName)[0]);
            }
            $_connectionName = $this->connection->getConfig('name');
            $columns = Db::connection($_connectionName)->getSchemaBuilder()->getColumnListing($_tableName);
            return $columns;
        });

        EloquentBuilder::macro('getColumns', function(){
            return $this->getQuery()->getColumns();
        });
        
        // Heartbeat
        if ($worker) {
            Timer::add(55, function () use ($connections) {
                foreach ($connections as $key => $item) {
                    if ($item['driver'] == 'mysql') {
                        Db::connection($key)->select('select 1');
                    }
                }
            });
        }
    }
}
