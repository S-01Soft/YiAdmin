<?php

namespace yi\logic;

use yi\Tree;
use support\exception\Exception;
use PDOException;
use support\Db;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AdminLogic extends BaseLogic
{
    protected $toggleFields = ['status', 'weigh', 'sort'];

    protected $where_ignore = [];

    public function paginateView($c)
    {
        $payload = (object) [
            'controller' => $c
        ];
        $this->event('PaginateView', $payload);
    }

    public function paginate()
    {
        $page_size = (int)request()->input('page_size', 10);
        $page = (int)request()->input('page', 1);
        $query = $this->getDefaultQuery();
        $payload = (object) [
            'query' => $query
        ];
        $this->beforePaginate($payload->query);
        $this->event('BeforePaginate', $payload);
        $result = $query->paginate($page_size, '*', 'page', $page);
        $payload->result = $result;
        $this->event('AfterPaginate', $payload);
        return $this->afterPaginate($payload->result);
    }

    protected function beforePaginate($query)
    {}

    protected function afterPaginate($result)
    {
        return $result;
    }

    public function all()
    {
        $query = $this->getDefaultQuery();
        $payload = (object) [
            'query' => $query
        ];
        $this->beforeAll($payload->query);
        $this->event('BeforeAll', $payload);
        $result = $query->get();
        $payload->result = $result;
        $this->event('AfterAll', $payload);
        return $this->afterAll($payload->result);
    }
    protected function beforeAll($query)
    {}

    protected function afterAll($result)
    {
        return $result;
    }

    public function selectView($c)
    {
        $payload = (object) [
            'controller' => $c
        ];
        $this->event('SelectView', $payload);
    }

    public function select()
    {
        $page_size = (int)request()->input('page_size', 10);
        $page = (int)request()->input('page', 1);
        $query = $this->getDefaultQuery();
        $payload = (object) [
            'query' => $query
        ];
        $this->beforeSelect($payload->query);
        $this->event('BeforeSelect', $payload);
        $result = $payload->query->paginate($page_size, '*', 'page', $page);
        $payload->result = $result;
        $this->event('AfterSelect', $payload);
        return $this->afterSelect($payload->result);
    }

    protected function beforeSelect($query) 
    {}

    protected function afterSelect($result)
    {
        return $result;
    }

    public function addView($c)
    {}

    public function postAdd($form = [])
    {
        Db::beginTransaction();
        try {
            $payload = (object) [
                'form' => $form
            ];
            $payload->form = $this->beforePostAdd($payload->form);
            $this->event('BeforePostAdd', $payload);
            $this->model = $this->static::create($payload->form);
            $result = $this->afterPostAdd($this->model, $payload->form);
            $payload->row = $this->model;
            $payload->result = $result;
            $this->event('AfterPostAdd', $payload);
            Db::commit();
            return $payload->result;
        } catch (Exception $e) {
            Db::rollBack();
            throw new Exception($e->getMessage(), $e->getCode());
        } catch (PDOException $e) {
            Db::rollBack();
            throw new Exception($e->getMessage());
        } catch (\Exception $e) {
            Db::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    protected function beforePostAdd($form)
    {
        return $form;
    }

    protected function afterPostAdd($model, $form = [])
    {
        return $model;
    }


    public function editView($c)
    {}

    public function getEdit($id)
    {
        $query = $this->static::where($this->model->getKeyName(), $id);
        $payload = (object) [
            'query' => $query,
            'row' => $this->model
        ];
        $this->beforeGetEdit($payload->query);
        $this->event('BeforeGetEdit', $payload);
        $result = $query->first();
        $result = $this->afterGetEdit($result);
        $payload->result = $result;
        $this->event('AfterGetEdit', $payload);
        return $payload->result;
    }

    protected function beforeGetEdit($query)
    {}

    protected function afterGetEdit($data) 
    {
        return $data;
    }

    public function postEdit($form)
    {
        Db::beginTransaction();
        try {
            $id = $form[$this->model->getKeyName()] ?? null;
            $query = $this->static::where($this->model->getKeyName(), $id);
            $payload = (object) [
                'query' => $query,
                'form' => $form
            ];
            $payload->form = $this->beforePostEdit($payload->form, $payload->query);
            $this->event('BeforePostEdit', $payload);
            $this->model = $query->first();
            $this->model->update($payload->form);
            $payload->result = $this->afterPostEdit($this->model, $payload->form);
            $payload->row = $this->model;
            $this->event('AfterPostEdit', $payload);
            Db::commit();
            return $payload->result;
        } catch (Exception $e) {
            Db::rollBack();
            throw new Exception($e->getMessage(), $e->getCode());
        } catch (PDOException $e) {
            Db::rollBack();
            throw new Exception($e->getMessage());
        } catch (\Exception $e) {
            Db::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    protected function beforePostEdit($form, $query)
    {
        return $form;
    }

    protected function afterPostEdit($model, $form = [])
    {
        return $model;
    }

    public function toggle($id, $params)
    {
        $this->model = $this->static::find($id);
        $data = [];
        foreach ($params as $k => $v) {
            if (in_array($k, $this->toggleFields)) $data[$k] = $v;
        }
        $payload = (object) [
            'data' => $data,
            'row' => $this->model
        ];
        $this->event('BeforeToggle', $payload);
        $payload->result = $this->beforeToggle($payload->row, $payload->data);
        $this->event('AfterToggle', $payload);
        return $this->model->update($payload->result);
    }

    protected function beforeToggle($model, $data)
    {
        return $data;
    }

    public function delete()
    {
        Db::beginTransaction();
        try {
            $query = $this->getDefaultQuery();
            $payload = (object) [
                'query' => $query
            ];
            $this->beforeDelete($payload->query);
            $this->event('BeforeDelete', $payload);
            $payload->query->delete();
            $this->afterDelete();
            $this->event('AfterDelete', $payload);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollBack();
            throw new Exception($e->getMessage(), $e->getCode());
        } catch (PDOException $e) {
            Db::rollBack();
            throw new Exception($e->getMessage());
        } catch (\Exception $e) {
            Db::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    protected function beforeDelete($query)
    {}

    protected function afterDelete()
    {}

    public function tree($pid_name = 'parent_id', $id_name = 'id', $id = 0)
    {
        $query = $this->getDefaultQuery();
        $payload = (object) [
            'query' => $query,
            'pid_name' => $pid_name,
            'id_name' => $id_name,
            'id' => $id
        ];
        $this->beforeQueryTree($payload->query);
        $this->event('BeforeTree', $payload);
        $list = $payload->query->get();
        $list = collect($this->afterQueryTree($list))->toArray();
        $payload->list = $list;
        $this->event('AfterTree', $payload);
        $tree = Tree::instance();
        $tree->init($payload->list, $payload->id_name, $payload->pid_name);
        return $tree;
    }

    protected function beforeQueryTree($query)
    {}

    protected function afterQueryTree($list)
    {
        return $list;
    }

    protected function beforeTreeArray($payload)
    {}
    
    public function tree_array($pid_name = 'parent_id', $id_name = 'id', $id = 0)
    {
        $payload = (object) [
            'pid_name' => $pid_name,
            'id_name' => $id_name,
            'id' => $id
        ];
        $this->event('BeforeTreeArray', $payload);
        $this->beforeTreeArray($payload);
        $tree = $this->tree($payload->pid_name, $payload->id_name, $payload->id);
        $payload->result = $tree->getTreeArray($payload->id);
        $this->event('AfterTreeArray', $payload);
        return $payload->result;
    }

    protected function beforeTreeList($payload)
    {}

    public function tree_list($field = 'name', $pid_name = 'parent_id', $id_name = 'id', $id = 0)
    {
        $payload = (object) [
            'field' => $field,
            'pid_name' => $pid_name,
            'id_name' => $id_name,
            'id' => $id
        ];
        $this->event('BeforeTreeList', $payload);
        $this->beforeTreeList($payload);
        $tree = $this->tree($payload->pid_name, $payload->id_name, $payload->id);
        $payload->result = $tree->getTreeList($tree->getTreeArray($payload->id), $payload->field);
        $this->event('AfterTreeList', $payload);
        return $payload->result;
    }

    public function export()
    {
        $params = request()->post();
        $fields = empty($params['fields']) ? [] : (array)json_decode(htmlspecialchars_decode($params['fields']), true);
        if (empty($fields)) throw new Exception(lang("No fields selected for export"));
        $query = $this->getDefaultQuery();
        $payload = (object) [
            'query' => $query
        ];
        $this->beforeExportQuery($payload->query);
        $this->event('BeforeExport', $payload);
        $result = $payload->query->limit($params['limit'])->get();
        $payload->result = $this->afterExportQuery($result);
        $this->event('AfterExport', $payload);
        if ($payload->result->isEmpty()) throw new Exception(lang("The data is empty"));
        $result = parse_dot_rows($payload->result->toArray(), array_column($fields, 'key'));
        $result = $this->parseFormat($result, $fields);
        return $this->afterExport($result);
    }

    protected function beforeExportQuery($query)
    {}

    protected function afterExportQuery($result)
    {
        return $result;
    }

    protected function afterExport($result)
    {
        return $result;
    }

    public function import()
    {
        $file = \yi\Storage::config(['driver' => 'local', 'record' => false, 'type' => 'private'])->upload(request()->file('file'));
        if ($file == 'continue') return $file;
        $reader = IOFactory::createReader('Xlsx');
        $excel = $reader->load($file);
        $sheet = $excel->getSheet(0);
        $maxRow = $sheet->getHighestRow();
        $maxCol = $sheet->getHighestDataColumn();
        $maxCol = Coordinate::columnIndexFromString($maxCol);
        $keys = [];
        for ($i = 0; $i < $maxCol; $i ++) {
            $v = $sheet->getCellByColumnAndRow($i + 1, 1)->getCalculatedValue();
            if (empty($v)) break;
            $keys[] = $v;
        }
        $data = [];
        for ($i = 0; $i < $maxRow - 2; $i ++) {
            $row = [];
            for ($j = 0; $j < count($keys); $j ++) {
                $row[$keys[$j]] = $sheet->getCellByColumnAndRow($j + 1, $i + 3)->getCalculatedValue();
            }
            $payload = (object) [
                'row' => $row
            ];
            event('ImportFormatRow', $payload);
            $this->event('ImportFormatRow', $payload);
            $data[] = $payload->row;
        }
        try {
            $this->model->insert($data);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function getDefaultQuery()
    {
        $param = request()->all();
        $query = $this->static::whereRaw("1=1");
        $this->parseWhere($query, $this->where_ignore);
        if (!empty($param['order'])) $query->orderByRaw($param['order']);
        if (!empty($param[$this->model->getKeyName()])) $query->where($this->model->getKeyName(), $param[$this->model->getKeyName()]);
        return $query;
    }

    protected function parseWhere($query, $ignore = [])
    {
        $where = empty(request()->get('where', '', [''])) ? request()->post('where', '', ['']) : request()->get('where', '', ['']);
        $where = $this->where = empty($where) ? [] : (is_array($where) ? $where : (array)json_decode($where));
        foreach ($where as $k => $v) {
            if (in_array($k, $ignore)) continue;
            if (is_array($v)) {
                if (count($v) < 2) break;
                $s = strtoupper($v[0]);
                $val = $v[1];    
                switch($s) {
                    case '=':
                    case '>':
                    case '<':
                    case '<>':
                    case '<=':
                    case '>=':
                        $query->where($k, $s, $val);
                        break;
                    case 'BETWEEN':
                        if (is_array($val)) {
                            $v1 = trim($val[0]); $v2 = trim($val[1]);
                            if ($v1 == '' && $v2 == '') break;
                            if ($v1 == '') {
                                $query->where($k, '<=', $val[1]);
                                break;
                            }
                            if ($v2 == '') {
                                $query->where($k, '>=', $val[0]);
                                break;
                            }
                        }
                        $query->whereBetween($k, $val);
                        break;
                    case 'NOT BETWEEN':
                        if (is_array($val)) {
                            $v1 = trim($val[0]); $v2 = trim($val[1]);
                            if ($v1 == '' && $v2 == '') break;
                            if ($v1 == '') {
                                $query->where($k, '>', $val[1]);
                                break;
                            }
                            if ($v2 == '') {
                                $query->where($k, '<', $val[0]);
                                break;
                            }
                        }
                        $query->whereNotBetween($k, $val);
                        break;
                    case 'LIKE':
                        $query->where($k, $s, "%$val%");
                        break;
                    case 'LEFT LIKE':
                        $query->where($k, 'LIKE', "%$val");
                        break;
                    case 'RIGHT LIKE':
                        $query->where($k, 'LIKE', "$val%");
                        break;
                    case 'IN':
                        $val = is_string($val) ? explode(',', $val) : $val;
                        $query->whereIn($k, $val);
                        break;
                    case 'NOT IN':
                        $val = is_string($val) ? explode(',', $val) : $val;
                        $query->whereNotIn($k, $val);
                        break;
                    case 'DATE':
                        $method = 'whereDate';
                    case 'MONTH':
                        $method = 'whereMonth';
                    case 'DAY':
                        $method = 'whereDay';
                    case 'YEAR':
                        $method = 'whereYear';
                    case 'TIME':
                        $method = 'whereTime';
                    case 'NULL':
                        $method = 'whereNull';
                    case 'NOTNULL':
                        $method = 'whereNotNull';
                        $query->$method($k, $val);
                        break;
                }
            } else {
                $query->where($k, $v);
            }
        }
    }

    protected function parseFormat($rows, $setting)
    {
        $setting = array_combine(array_column($setting, 'key'), $setting);
        foreach ($rows as &$row) {
            foreach ($row as $k => $v) {
                $format = $setting[$k]['format'] ?? 'default';
                $format = strtoupper($format);
                switch ($format) {
                    case 'DATE':
                        if (!empty($v)) $row[$k] = date('Y-m-d H:i:s', $v);
                        else $row[$k] = '';
                        break;
                    default :
                        $payload = (object) [
                            'format' => $format,
                            'row' => $row,
                            'k' => $k,
                            'v' => $v
                        ];
                        event('ExportsFormatValue', $payload);
                        $this->event('ExportsFormatValue', $payload);
                        $row = $payload->row;
                        break;
                }
            }
        }
        return $rows;
    }

    protected function event($name, $payload = null)
    {
        event(request()->getName() . $name, $payload);
    }
}