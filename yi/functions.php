<?php

use support\exception\Exception;
use support\Container;
use support\Response;
use support\Str;
use support\Cache;
use support\Db;
use yi\Module;
use yi\Validate;

spl_autoload_register(function($class) {
    $namespaces = ev('CustomNamespaces');
    if (empty($namespaces)) return;
    foreach ($namespaces as $namespace => $paths) {
        if (strpos($class, $namespace) === 0) {
            $f = substr($class, strlen($namespace) + 1);
            foreach ($paths as $path) {
                $file = app_path() . DS . $path . $f . '.php';
                $file = str_replace(['/', '\\'], [DS, DS], $file);
                if (is_file($file)) {
                    include_once $file;
                }
            }
        }
    }
});

function add_namespace($namespace, $path = null)
{
    if (is_array($namespace)) {
        foreach ($namespace as $k => $v) {
            ev('AddNamespace', $k, $v);
        }
    } else {
        ev('AddNamespace', $namespace, $path);
    }
}

function app($name = null)
{
    $app = Container::get(yi\App::class);
    if ($name) return $app->get($name);
    return $app;
}

function event($name, $payload = [])
{
    Container::get(\yi\Event::class)->dispatch($name, $payload);
}

function ev()
{
    $args = func_get_args();
    $name = $args[0];
    unset($args[0]);
    $payload = (object) [
        'params' => array_values($args),
        'result' => null
    ];
    event($name, $payload);
    return $payload->result;
}

function cache()
{
    $args = func_get_args();
    switch (count($args)) {
        case 0: 
            return Cache::instance();
        case 1:
            return Cache::get($args[0]);
        case 2:
            Cache::set(...$args);
            break;
    }
}

function get_current_theme()
{
    return get_module_group_config('system', 'base', 'theme');
}

function get_lang()
{
    return request() ? request()->var('lang') : new \yi\Lang;
}

function error($message = '', $code = 10000, $data = null, $type = null, $error_tmpl = null, $url = null)
{
    if ($type == 'json' || request()->isAjax() || request()->expectsJson()) {
        $result = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];
        $response = json($result);
    } else {
        $error_tmpl = $error_tmpl ?: config('app.dispatch_error_tmpl');
        $url = $url ?: request()->header('REFERER') ?: '/';
        $response = new Response(200, [], config('view.handler')::render($error_tmpl, ['message' => $message, 'code' => $code, 'url' => $url]));
    }
    event('ErrorResponse', $response);
    event('Response', $response);
    return $response;
}

function success($data = [], $message = '', $callback = null)
{
    $response = json([
        'code' => 1,
        'data' => $data,
        'message' => $message,
    ]);
    if ($callback instanceof \Closure) $callback($response);
    event('SuccessResponse', $response);
    event('Response', $response);
    return $response;
}

function fetch($template, $vars)
{
    $response = view($template, $vars);
    event('FetchResponse', $response);
    event('Response', $response);
    return $response;
}

function validate($validate = '', array $message = [], bool $batch = false, bool $failException = true): Validate
{
    if (is_array($validate) || '' === $validate) {
        $v = new Validate();
        if (is_array($validate)) {
            $v->rule($validate);
        }
    } else {
        if (strpos($validate, '.')) {
            // 支持场景
            [$validate, $scene] = explode('.', $validate);
        }
        $class = false !== strpos($validate, '\\') ? $validate : app()->parseClass('validate', $validate);

        $v = new $class();
        if (!empty($scene)) {
            $v->scene($scene);
        }
    }
    $v->setLang(get_lang());
    $v->setDb(new Db);
    return $v->message($message)->batch($batch)->failException($failException);
}

function get_version()
{
    return trim(file_get_contents(base_path() . DS . 'version'));
}

function is_installed()
{
    return file_exists(runtime_path() . DS . 'install.lock');
}

function lang(string $id, array $parameters = [], string $domain = null, string $locale = null)
{
    return trans($id, $parameters, $domain, $locale);
}

function logs($channel = 'default')
{
    return \support\Log::channel($channel);
}

function run_command($name, $args = '')
{
    $shell = "php " . base_path() . DS . "cli " . $name . ' ' . $args;
    return shell_exec($shell);
}

function get_admin()
{
    return request()->admin;
}

function get_user()
{
    return request()->user;
}

function get_ip()
{
    return request() ? request()->getRealIp() : '127.0.0.1';
}

function refresh_module_config()
{
    return app(Module::class)->refreshConfig();
}

function refresh_module_info()
{
    return app(Module::class)->refreshInfo();
}

function refresh_modules()
{
    return app(Module::class)->refresh();
}

function get_module_info($name)
{
    return app(Module::class)->getInfo($name);
}

function get_full_module_list()
{
    return app(Module::class)->getList();
}

function get_module_list()
{
    $list = get_full_module_list();
    if (isset($list['system'])) unset($list['system']);
    return $list;
}

function get_module_full_config($name)
{
    return app(Module::class)->getFullConfig($name);
}

function get_module_config($name)
{
    return app(Module::class)->getConfig($name);
}

function get_module_group_config($name, $group = null, $key = null)
{
    return app(Module::class)->getGroupConfig($name, $group, $key);
}

function set_module_full_config($name, $config)
{
    return app(Module::class)->setFullConfig($name, $config);
}

function set_module_config($name, $form)
{
    return app(Module::class)->setConfig($name, $form);
}

function set_module_info($name, $info)
{
    return app(Module::class)->setInfo($name, $info);
}

function module_exists($name)
{
    return !empty(get_module_info($name));
}

function widget($name, $param = [], $template = '')
{
    return \yi\Widget::init()->render($name, $param, $template);
}

function get_view()
{
    return config('view.handler');
}

function get_template()
{
    $args = func_get_args();
    if (count($args) == 1) {
        $template = $args[0];
        if (Str::endsWith($template, '.html')) return BASE_PATH . DS .$template;
        $default = view_path() . DS . 'default' . DS . str_replace('/', DS, $template ) . '.html';
        $tpl = view_path() . DS . get_current_theme() . DS . $template . '.html';
        if (!file_exists($tpl)) $tpl = $default;
        return $tpl;
    } elseif (count($args) == 2) {
        list($module, $template) = $args;
        return app_path() . DS . $module . DS . 'view' . DS . $template . '.html';
    }
}

function paginator($paginator)
{
    return (new \support\Paginator($paginator))->render();
}

function write_ini_file($assoc_arr, $path, $has_sections = FALSE) { 
    $content = ""; 
    if ($has_sections) { 
        foreach ($assoc_arr as $key => $elem) {
            if (!is_array($elem)) {
                $content .= "$key = $elem" . PHP_EOL;
            } else {
                $content .= "[" . $key . "]" . PHP_EOL; 
                foreach ($elem as $key2 => $elem2) { 
                    if(is_array($elem2)) 
                    { 
                        for($i=0;$i<count($elem2);$i++) 
                        { 
                            $content .= $key2 . "[] = " . $elem2[$i] . PHP_EOL; 
                        } 
                    } 
                    else $content .= $key2 . " = " . $elem2 . PHP_EOL; 
                } 

            }
        } 
    } 
    else { 
        foreach ($assoc_arr as $key => $elem) { 
            if(is_array($elem)) 
            { 
                for($i = 0; $i < count($elem); $i++) 
                { 
                    $content .= $key . "[] = " . $elem[$i] . PHP_EOL; 
                } 
            } 
            else $content .= $key . " = " . $elem . PHP_EOL; 
        } 
    } 
    mkfile($path, $content);
    return true; 
}

function parse_ini_str($str) {
   
    if(empty($str)) return false;

    $lines = explode("\n", $str);
    $ret = Array();
    $inside_section = false;
    foreach($lines as $line) {
        $line = trim($line);
        if(!$line || $line[0] == "#" || $line[0] == ";") continue;
        if($line[0] == "[" && $endIdx = strpos($line, "]")){
            $inside_section = substr($line, 1, $endIdx-1);
            continue;
        }
        if(!strpos($line, '=')) continue;
        $tmp = explode("=", $line, 2);
        if($inside_section) {
            $key = rtrim($tmp[0]);
            $value = ltrim($tmp[1]);
            if(preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
                $value = mb_substr($value, 1, mb_strlen($value) - 2);
            }
            $t = preg_match("^\[(.*?)\]^", $key, $matches);
            if(!empty($matches) && isset($matches[0])) {
                $arr_name = preg_replace('#\[(.*?)\]#is', '', $key);
                if(!isset($ret[$inside_section][$arr_name]) || !is_array($ret[$inside_section][$arr_name])) {
                    $ret[$inside_section][$arr_name] = array();
                }
                if(isset($matches[1]) && !empty($matches[1])) {
                    $ret[$inside_section][$arr_name][$matches[1]] = $value;
                } else {
                    $ret[$inside_section][$arr_name][] = $value;
                }
            } else {
                $ret[$inside_section][trim($tmp[0])] = $value;
            }
        } else {
            $ret[trim($tmp[0])] = ltrim($tmp[1]);
        }
    }
    return $ret;
}


function mkfile($path, $content = "", $mode = 0777) {
    $tmp_arr = explode(DS, $path);
    array_pop($tmp_arr);
    $dir = implode(DS, $tmp_arr);
    if (!is_file($path)) {
        if (!is_dir($dir)) mkdir($dir, $mode, true);
        file_put_contents($path, $content);
    } else file_put_contents($path, $content);
}

function rmdirs($path, $self = true) 
{
    scan_dir($path, function($it, $iterator) {
        $file = $it->getRealPath();
        if ($it->isDir()) @rmdir($file);
        else @unlink($file);
    }, false);
    if ($self) @rmdir($path);
}

function copy_files($source, $dest) 
{
    if (!is_dir($dest)) mkdir($dest, 0755, true);
    scan_dir($source, function($it, $iterator) use ($dest) {
        $path = $dest . DS . $iterator->getSubPathName();
        if ($it->isDir()) {
            if (!is_dir($path)) mkdir($path, 0755, true);
        } else {
            copy($it, $path);
        }
    });
}

function scan_dir($dir, $cb, $self_first = true)
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        $self_first ? RecursiveIteratorIterator::SELF_FIRST : RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $it) {
        if ($cb) {
            $result = $cb($it, $iterator);
            if ($result === false) return;
        }
    }
}
function array_merge_deep(...$arrs)
{
    $merged = [];
    while ($arrs) {
        $array = array_shift($arrs);
        if (!$array) {continue;}
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                if (is_array($value) && array_key_exists($key, $merged)
                    && is_array($merged[$key])) {
                    $merged[$key] = array_merge_deep(...[$merged[$key], $value]);
                } else {
                    $merged[$key] = $value;
                }
            } else {
                $merged[] = $value;
            }
        }
    }

    return $merged;
}

function snake_controller($controller, $separator = '/')
{
    $result = [];
    foreach (explode('/', str_replace('\\', '/', $controller)) as $v) {
        $result[] = Str::snake($v);
    }
    return implode($separator, $result);
}

function studly_controller($controller, $separator = '/')
{
    $result = [];
    foreach (explode('/', str_replace('\\', '/', $controller)) as $v) {
        $result[] = Str::studly($v);
    }
    return implode($separator, $result);
}

function find_rows(Array $rows, Array $search) 
{
    foreach ($rows as $i => $row) {
        $result = true;
        foreach ($search as $k => $v) {
            if (!isset($row[$k])) return -1;
            if ($row[$k] != $v) {
                $result = false;
                break 1;
            }
        }
        if ($result) return $i;
    }
    return -1;
}

function reverse_descartes(Array $rows) 
{
    if (!empty($rows)) {
        $colCount = count($rows[0]);
        $collection = collect($rows);
        $result = [];
        while ($colCount > 0) {
            $colCount --;
            $col = $collection->pluck($colCount);
            $item = [];
            foreach($col as $v) {
                if (!in_array($v, $item)) $item[] = $v;
            }
            array_unshift($result, $item);
        }
        return $result;
    }
    return $rows;
}

function array_integer(Array $array) 
{ 
    foreach ($array as $i => $v) {
        $array[$i] = intval($v);
    }
    return $array;
}

function rows_merge_same_key($rows, $key, $merge_key) 
{
    $new_rows = [];
    foreach ($rows as $row) {
        $index = find_rows($new_rows, [$key => $row[$key]]);
        if ($index > -1) {
            $new_rows[$index][$merge_key] = array_merge($new_rows[$index][$merge_key], $row[$merge_key]);
        } else $new_rows[] = $row;
    }
    return $new_rows;
}

function rows_group_by($rows, $keys, $symbol = '___') 
{
    $result = [];
    foreach ($rows as $i => $row) {
        $new_key = [];
        foreach ($keys as $k) {
            $new_key[] = $row[$k];
        }
        $new_key = implode($symbol, $new_key);
        $result[$new_key][] = $row;
    }
    return $result;
}

function parse_dot_row($row, $key) {
    $arr = explode('.', $key);
    foreach($arr as $v) {
        if (is_array($row) && isset($row[$v])) $row = $row[$v];
        elseif (is_object($row) && property_exists($row, $v)) $row = $row->$v;
        else return null;
    }
    return $row;
}

function parse_dot_rows($rows, $keys)
{
    $result = [];
    foreach ($rows as $row) {
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = parse_dot_row($row, $key);
        }
        $result[] = $data;
    }
    return $result;
}

function format_bytes($size, $delimiter = '') 
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter ."&nbsp;".$units[$i];
}

function filter($value, $callbacks = [])
{
    if (empty($callbacks)) return $value;
    if (is_string($callbacks)) $callbacks = explode(',', $callbacks);
    if ($callbacks instanceof Closure) $callbacks = [$callbacks];
    if (!$value) {
        return $value;
    }
    if (is_array($value)) {
        array_walk_recursive($value, function(&$item) use ($callbacks) {
            if (is_string($item)) {
                foreach ($callbacks as $cb) {
                    if (is_callable($cb)) $item = call_user_func($cb, $item);
                }
            }
        });
    } else {
        foreach ($callbacks as $cb) {
            if (is_callable($cb)) call_user_func($cb, $value);
        }
    }
    return $value;
}

function fixurl($url)
{
    if (preg_match("/^http[s]{0,1}:\/\//i", $url) || preg_match("/^\/\//i", $url)) return $url;
    $default_proto = get_module_group_config('system', 'base', 'proto');
    $proto = request() ? (request()->header('X-Forwarded-Proto') ?: $default_proto) : $default_proto;
    $host = request() ? request()->host() : get_module_group_config('system', 'base', 'host');
    return ($proto ? $proto . ':' : '') . '//' . $host . (Str::startsWith($url, '/') ? '' : '/') . $url;
}

function view_path()
{
    return base_path() . DS . 'view';
}

function version_sort($versions, $asc = true) {
    $source = [
        'maps' => [],
        'item_length' => [],
        'list' => [],
        'sort_arr' => []
    ];
    foreach ($versions as $v) {
        $arr = explode('.', $v);
        $source['list'][$v] = $arr;
        foreach ($arr as $i => $a) {
            $l = $source['item_length'][$i] ?? 0;
            $source['item_length'][$i] = strlen($a) > $l ? strlen($a) : $l;
        }
    }
    foreach ($source['list'] as $origin => $arr) {
        $t = [];
        foreach ($arr as $i => $a) {
            $t[$i] = str_pad($a, $source['item_length'][$i], 0, STR_PAD_LEFT);
        }
        $source['sort_arr'][] = implode('.', $t);
        $source['maps'][implode('.', $t)] = $origin;
    }
    if ($asc) {
        sort($source['sort_arr']);
    } else {
        rsort($source['sort_arr']);
    }
    $result = [];
    foreach ($source['sort_arr'] as $v) {
        $result[] = $source['maps'][$v];
    }
    return $result;
}


function split_sql($file)
{
    if (!file_exists($file)) return [];
    $prefix = get_db_config()['prefix'];
    $sql = "";
    $result = [];
    foreach (file($file) as $line) {
        $line = str_replace(["`__PREFIX__", "\r\n"], ['`' . $prefix, "\n"], $line);
        $sql .= $line;
        if (Str::endsWith(trim($line), ";")) {
            $result[] = trim($sql);
            $sql = "";
        }
    }
    return $result;
}

function get_db_config()
{
    return config('database.connections.' . config('database.default'));
}

function load_script($type, $path)
{
    $requires = request()->config($type, 'requires') ?: [];
    if (is_array($path)) $requires = array_merge($requires, $path);
    else $requires[] = $path;
    request()->config($type, 'requires', $requires);
}

function http_user_agent()
{
    return request()->header('USER-AGENT');
}

function is_mobile()
{
    if (preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', request()->header('USER-AGENT'))) return true;
    return false;
}

function is_wechat()
{
    return strpos(http_user_agent(), 'MicroMessenger') !== false;
}

function is_android()
{
    return strpos(http_user_agent(), 'Android') !== false;
}

function is_ios()
{
    return strpos(http_user_agent(), 'iPhone') !== false || strpos(http_user_agent(), 'iPad') !== false;
}

function is_iphone()
{
    return strpos(http_user_agent(), 'iPhone') !== false;
}

function is_ipad()
{
    return strpos(http_user_agent(), 'iPad') !== false;
}

function token($name = '__csrf_token__')
{
    return request()->buildToken($name);
}

function token_meta($name = '__csrf_token__')
{
    $token = request()->buildToken($name);
    return '<meta name="csrf-token" content="' . $token . '">';
}

function get_static($name, $default)
{
    $config = get_module_group_config('system', 'statics');
    if (!empty($config['open']) && !empty($config['maps'][$name])) return $config['maps'][$name];
    return $default;
}

function get_system_status()
{
    return \yi\System::status();
}

function system_reload()
{
    \yi\System::reload();
}

function send_email($address, $subject, $message, $config)
{
    $mail = new \PHPMailer\PHPMailer\PHPMailer();
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();
    $mail->IsHTML(true);
    //$mail->SMTPDebug = 3;
    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet = 'UTF-8';
    
    if (is_string($address)) $address = explode(',', $address);
    foreach ($address as $addr) {
        $mail->AddAddress($addr);
    }
    // 设置邮件正文
    $mail->Body = $message;
    // 设置邮件头的From字段。
    $mail->From = $config['from_email'];
    // 设置发件人名字
    $mail->FromName = $config['from_name'];
    // 设置邮件标题
    $mail->Subject = $subject;
    // 设置SMTP服务器。
    $mail->Host = $config['smtp'];
    //by Rainfer
    // 设置SMTPSecure。
    $Secure = $config['smtp_secure'];
    $mail->SMTPSecure = empty($Secure) ? '' : $Secure;
    // 设置SMTP服务器端口。
    $port = $config['smtp_port'];
    $mail->Port = empty($port) ? "25" : $port;
    // 设置为"需要验证"
    $mail->SMTPAuth    = true;
    $mail->SMTPAutoTLS = false;
    $mail->Timeout     = 10;
    // 设置用户名和密码。
    $mail->Username = $config['send_email'];
    $mail->Password = $config['send_password'];
    // 发送邮件。
    if (!$mail->Send()) {
        throw new Exception($mail->ErrorInfo);
    } else {
        return true;
    }
}

function captcha()
{
    // 初始化验证码类
    $builder = new \Gregwar\Captcha\CaptchaBuilder;
    // 生成验证码
    $builder->build();
    request()->session()->set('captcha', strtolower($builder->getPhrase()));
    // 获得验证码图片二进制数据
    $img_content = $builder->get();
    
    // 输出验证码二进制数据
    return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
}

function captcha_img()
{
    return "<img src='/captcha.html' onclick='this.src=\"/captcha.html?\"+Math.random();' />";
}

function get_table_info($table)
{
    $prefix = get_db_config()['prefix'];
    $result = Db::select("SHOW FULL COLUMNS FROM {$prefix}{$table}");
    return $result;
}

function get_table_key_name($table)
{
    foreach (get_table_info($table) as $item) {
        if ($item->Key == 'PRI') return $item->Field;
    }
    return '';
}

if (!function_exists('str_to_kv')) {
    function str_to_kv($str)
    {
        $result = [];
        $str = preg_replace("/[,|\s]+/", ",", trim($str));
        $data = explode(',', $str);
        foreach ($data as $v) {
            $arr = explode('=', $v, 2);
            if (count($arr) == 2) {
                $result[$arr[0]] = is_numeric($arr[1]) ? ($arr[1] + 0) : $arr[1];
            }
        }
        return $result;
    }
}