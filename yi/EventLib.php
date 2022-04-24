<?php

namespace yi;

use support\Db;
use yi\Event;
use app\system\model\admin\EventModel;
use app\system\model\admin\EventAppsModel;

class EventLib
{
    protected $listeners = null;
    
    public $events = [];
    
    static $_instance = null;
    
    /**
    * @param array $options 参数
    * @return EventLib
    */
    public static function instance()
    {
        if (!static::$_instance) static::$_instance = new static;
        return static::$_instance;
    }

    public function add(array $events)
    {
        foreach ($events as $i => $event) {
            $event = is_array($event) ? $event : [$event];
            if (empty($this->events[$i])) {
                $this->events[$i] = $event;
            } else {
                $this->events[$i] = array_unique(array_merge($this->events[$i], $event));
            }
        }
    }

    public function bind(array $events = [])
    {
        $this->add($events);
        app(Event::class)->bindEvents($this->events);
    }

    public function importevents($appPath)
    {
        $list = $this->getEventData($appPath);
        if (empty($list)) return;
        $events_data = [];
        $event_app_data = [];
        foreach ($list as $row) {
            $childList = $row['childList'];
            foreach ($childList as $i => $item) {
                $childList[$i]['event'] = $row['event'];
            }
            $event_app_data = array_merge($event_app_data, $childList);
            unset($row['childList']);
            $events_data[] = $row;
        }
        $event_list = Db::table('event')->whereIn('event', array_column($events_data, 'event'))->get()->map(function($row) {
            return (array)$row;
        })->toArray();
        if (!empty($event_list)) {
            foreach ($events_data as $i => $row) {
                if (find_rows($event_list, ['event' => $row['event']]) > -1) {
                    unset($events_data[$i]);
                }
            }
        }
        Db::beginTransaction();
        try {            
            Db::table('event_apps')->where('app_name', $this->appInfo['name'])->delete();
            Db::table('event')->insert($events_data);
            Db::table('event_apps')->insert($event_app_data);
            Db::commit();
            return true;
        } catch (\PDOException $e) {
            Db::rollBack();
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            Db::rollBack();
            throw $e;
        }
    }

    private function getEventData($appPath)
    {
        if (!is_file($appPath . DS . 'events.json')) return [];
        $content = json_decode(file_get_contents($appPath . DS . 'events.json'), true);
        $this->appInfo = parse_ini_file($appPath . DS . "info.ini");
        $result = [];
        foreach ($content as $row) {
            $result = array_merge($result, $this->parseEvent($row['group_title'], $row['list']));
        }
        $result = rows_merge_same_key($result, 'event', 'childList');
        return $result;
    }

    private function parseEvent($groupName, $events)
    {
        $eventList = [];
        $eventListeners = [];
        $result = [];
        foreach ($events as $i => $row) {
            $data = [
                'group' => $groupName,
                'event' => $row['event'],
                'event_desc' => $row['description'],
                'payload' => isset($row['payload']) ? $row['payload'] : ''
            ];
            $childList = [];
            foreach ($row['listeners'] as $item) {
                $childList[] = [
                    'class_name' => $item['name'],
                    'class_desc' => $item['description'],
                    'sort' => isset($item['sort']) ? $item['sort'] : 10000,
                    'status' => isset($item['status']) ? $item['status'] : 1,
                    'app_name' => $this->appInfo['name'],
                    'app_title' => $this->appInfo['title']
                ];
            }
            $data['childList'] = $childList;
            $result[] = $data;
        }
        return $result;
    }

    private function checkSameClass($list)
    {
        $classes = [];
        foreach ($list as $row) {
            foreach ($row['childList'] as $childRow) {
                $index = find_rows($classes, ['class_name' => $childRow['class_name']]);
                if ($index > -1) {
                    $class = $classes[$index];
                    return "存在相同的执行类：" . $class['class_name'] . " " . $class['app_name'] . " " . $childRow['app_name'];
                }
                $classes[] = [
                    'class_name' => $childRow['class_name'],
                    'app_name' => $childRow['app_name']
                ];
            }
        }
        return true;
    }

    /**
     * 启用
     */
    public function enable($app_name)
    {
        $path = app_path() . DS . $app_name;
        $this->importevents($path);
        $this->clear();
    }

    /**
     * 禁用
     */
    public function disable($app_name)
    {
        Db::table('event_apps')->where('app_name', $app_name)->update(['status' => 0]);
        $this->clear();
    }

    /**
     * 插件卸载
     */
    public function uninstall($app_name)
    {
        Db::table('event_apps')->where('app_name', $app_name)->delete();
        $this->clear();
    }

    public function syncListeners()
    {
        $list = Db::table('event')->join('event_apps', 'event.event', '=', 'event_apps.event')->where('event_apps.status', "1")->select('event.*', 'event_apps.class_name', 'event_apps.app_name', 'event_apps.sort', 'event_apps.status')->orderBy('event.id', 'ASC')->orderBy('event_apps.sort', 'DESC')->orderBy('event_apps.id', 'ASC')->get()->map(function($row) {
            return (array)$row;
        })->toArray();
        $newList = [];
        foreach ($list as $row) {
            if (empty($newList[$row['event']])) {
                $newList[$row['event']] = [$row['class_name']];
            } else $newList[$row['event']][] = $row['class_name'];
        }
        
        $this->listeners = $newList;
        return $this;
    }

    public function setListeners()
    {
        $this->syncListeners();
    }

    public function getListeners()
    {
        if (is_null($this->listeners)) $this->setListeners();
        return $this->listeners;
    }

    public function clear()
    {
        $this->syncDelete();
        ev('SyncData', __CLASS__, 'syncDelete');
        return $this;
    }

    public function syncDelete()
    {
        $this->listeners = null;
        $this->events = [];
    }
}