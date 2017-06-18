<?php
// +----------------------------------------------------------------------
// | ApePHP [ a lightweight php framework ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.xiaocp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Ape <me@xiaocp.com>
// +----------------------------------------------------------------------

namespace ape;

class Route{

    // 路由规则
    private static $rules = [
        'get'     => [],
        'post'    => [],
        'put'     => [],
        'delete'  => [],
        'patch'   => [],
        'head'    => [],
    ];

    private static $_default_module;
    private static $_default_controller;
    private static $_default_action;

    protected function __construct(){

    }

    /**
     * 分隔符
     * @var string
     */
    protected static $depr = '/';

    // init route
    private static function init(){

        foreach (App::$_route as $key => $value){
            if(array_key_exists($key, self::$rules))
                self::$rules[$key] = array_merge(self::$rules[$key], $value);
            else{
                // 获取请求method
                if($i = strpos($key, '@')){
                    $new_key = substr($key, 0, $i);
                    if(array_key_exists($new_key, self::$rules)){
                        self::$rules[$new_key] = array_merge(self::$rules[$new_key], [substr($key, $i + 1) => $value]);
                    }
                }else{
                    self::$rules['get'] = array_merge(self::$rules['get'], [$key => $value]);
                }
            }
        }

    }

    // check route
    private static function check($method = 'get'){
        // 是否开启路由
        $url_route_on = Config::get('url_route_on');
        if(!$url_route_on)
            return ;

        $url = Request::instance()->baseUrl();
        if(substr($url, 0, 1) == '/')
            $url = substr($url, 1);
        if(substr($url, -1) == '/')
            $url = substr($url,0,-1);
        if(!$url) $url = '/';

        foreach (self::$rules[$method] as $key => $value){
            // 匹配url
            if(strpos($key, '/:')){
                $array = explode("/:", $key);
                foreach ($array as $k => $v){
                    if($k == 0)
                        $url_suf = $v;
                    else
                        $key = str_replace(':'.$v, '(.+)',$key);
                }
                $result = preg_match('/'.str_replace('/', '\/', $key).'/', $url, $param_value);
                if($result && $param_value){
                    $return = false;
                    foreach ($param_value as $k => $v){
                        if($k == 0) continue;
                        if(strpos($v, '/'))
                            $return = true;
                        Request::instance()->get([$array[$k] => $param_value[$k]]);
                    }
                    if($return) continue;
                    $varray = preg_split("/(\/|@)/",$value);
                    $module = $varray[0];
                    $controller = $varray[1];
                    $action = $varray[2];
                    Request::instance()->get(Config::get('url_route_module'), $module);
                    Request::instance()->get(Config::get('url_route_controller'), $controller);
                    Request::instance()->get(Config::get('url_route_action'), $action);
                    self::dispatch($module, $controller, $action);
                }
            }else if($url == $key){
                $varray = preg_split("/(\/|@)/",$value);
                $module = $varray[0];
                $controller = $varray[1];
                $action = $varray[2];
                Request::instance()->get(Config::get('url_route_module'), $module);
                Request::instance()->get(Config::get('url_route_controller'), $controller);
                Request::instance()->get(Config::get('url_route_action'), $action);
                self::dispatch($module, $controller, $action);
            }
        }

    }

    /**
     * 开始
     */
    public static function start(){
        // 获取url路由模式
        $url_route_mode = Config::get('url_route_mode');

        // 获取分隔符
        self::$depr = Config::get('pathinfo_depr');
        self::init();

        // 获取请求方式
        $method = Request::instance()->method(true);
        switch ($method) {
            case 'POST':
                self::check('post');
                break;
            case 'PUT':
                self::check('put');
                break;
            case 'DELETE':
                self::check('delete');
                break;
            case 'PATCH':
                self::check('patch');
                break;
            default:
                self::check();
                break;
        }

        // 获取默认控制器...
        self::$_default_module = Config::get('default_module');
        self::$_default_controller = Config::get('default_controller');
        self::$_default_action = Config::get('default_action');

        switch ($url_route_mode){
            // 传统模式 index.php?md=module&c=controller&m=method&param=value...
            case 1:
                self::tradition();
                break;
            // 兼容模式 index.php?s=module/controller/method/param/value...
            case 2:
                self::pathinfo();
                break;
            // module/controller/method?param=value...
            case 3:
                self::cool();
                break;
            // module/controller/method/param/value...
            case 4:
                self::mode();
                break;
            default:
                self::tradition();
                break;
        }
    }

    // 传统模式
    public static function tradition(){
        // 获取配置的参数名称
        $md = Config::get('url_route_module');
        $c = Config::get('url_route_controller');
        $m = Config::get('url_route_action');

        // 默认 请求 控制器 方法
        $module =& self::$_default_module;
        $controller =& self::$_default_controller;
        $action =& self::$_default_action;

        // 获取request参数
        $request = Request::instance();
        $params = $request->queryArray();
        !isset($params[$md]) || empty($params[$md]) ?: $module = $params[$md];
        !isset($params[$c]) || empty($params[$c]) ?: $controller = $params[$c];
        !isset($params[$m]) || empty($params[$m]) ?: $action = $params[$m];

        self::dispatch($module, $controller, $action);
    }

    // 兼容模式
    public static function pathinfo(){
        $pathinfo = Config::get('var_pathinfo');
        $url = Request::instance()->get($pathinfo);
        $url = $url ?: '';

        $module =& self::$_default_module;
        $controller =& self::$_default_controller;
        $action =& self::$_default_action;

        // 如果参数前面有 / 则剔除
        if(substr($url, 0, 1) == '/')
            $url = substr($url, 1);

        // 获取模块名称
        if($arg1 = strstr($url, self::$depr, true))
            $url = substr($url, strpos($url, self::$depr) + 1);
        else{
            $arg1 = $url;
            $url = '';
        }
        // 控制器名称
        if($arg2 = strstr($url, self::$depr, true))
            $url = substr($url, strpos($url, self::$depr) + 1);
        else{
            $arg2 = $url;
            $url = '';
        }
        // 方法名称
        $arg3 = strstr($url, self::$depr, true) ?: $url;

        empty($arg1) ?: $module = $arg1;
        empty($arg2) ?: $controller = $arg2;
        empty($arg3) ?: $action = $arg3;

        $param = $url = substr($url, strpos($url, self::$depr) + 1);
        $params = explode(self::$depr, $param);
        $size = count($params);
        for ($i = 0; $i < $size; $i+=2){
            // 把参数设置到request对象传递到指定的方法
            !isset($params[$i]) ?: Request::instance()->get([$params[$i] => isset($params[$i+1]) ? $params[$i+1] : '']);
        }

        // 设置url信息
        Request::instance()->get(Config::get('url_route_module'), $module);
        Request::instance()->get(Config::get('url_route_controller'), $controller);
        Request::instance()->get(Config::get('url_route_action'), $action);

        self::dispatch($module, $controller, $action);
    }

    // 路由模式3
    public static function cool(){

        $url = Request::instance()->baseUrl();
        if(substr($url, 0, 1) == '/')
            $url = substr($url, 1);
        if(substr($url, -1) == '/')
            $url = substr($url,0,-1);

        $c = substr_count($url, self::$depr);

        $module =& self::$_default_module;
        $controller =& self::$_default_controller;
        $action =& self::$_default_action;

        switch ($c){
            // 访问 module
            case 0:
                $url && $module = $url;
                break;
            // 访问 module/controller
            case 1:
                list($req_module, $req_controller) = explode(self::$depr, $url);
                $req_module && $module = $req_module;
                $req_controller && $controller = $req_controller;
                break;
            // 访问 module/controller/action
            case 2:
                list($req_module, $req_controller, $req_action) = explode(self::$depr, $url);
                $req_module && $module = $req_module;
                $req_controller && $controller = $req_controller;
                $req_action && $action = $req_action;
                break;
            default:
                // todo 错误处理
                break;
        }

        // 设置url信息
        Request::instance()->get(Config::get('url_route_module'), $module);
        Request::instance()->get(Config::get('url_route_controller'), $controller);
        Request::instance()->get(Config::get('url_route_action'), $action);

        self::dispatch($module, $controller, $action);

    }

    // 路由模式4
    public static function mode(){
        $url = Request::instance()->baseUrl();
        if(substr($url, 0, 1) == '/')
            $url = substr($url, 1);
        if(substr($url, -1) == '/')
            $url = substr($url,0,-1);

        $module =& self::$_default_module;
        $controller =& self::$_default_controller;
        $action =& self::$_default_action;

        // 截取 / 符号出现的第三次位置前的字符
        $n = 0;
        for($i = 1; $i <= 3; $i++) {
            $n = strpos($url, self::$depr, $n);
            $i != 3 && $n++;
            if(!$n){
                $n --;
                break;
            }
        }
        // 截取路由
        $n ? $arg = substr($url, 0, $n) : $arg = $url;
        // 截取参数
        $param = !$n ? '' : substr($url, $n + 1);
        $c = substr_count($arg, self::$depr);
        switch ($c){
            // 访问 module
            case 0:
                $url && $module = $url;
                break;
            // 访问 module/controller
            case 1:
                list($req_module, $req_controller) = explode(self::$depr, $url);
                $req_module && $module = $req_module;
                $req_controller && $controller = $req_controller;
                break;
            // 访问 module/controller/action
            case 2:
                list($req_module, $req_controller, $req_action) = explode(self::$depr, $url);
                $req_module && $module = $req_module;
                $req_controller && $controller = $req_controller;
                $req_action && $action = $req_action;
                break;
            default:
                // todo 错误处理
                break;
        }

        $params = explode(self::$depr, $param);
        $size = count($params);
        for ($i = 0; $i < $size; $i+=2){
            // 把参数设置到request对象传递到指定的方法
            !isset($params[$i]) ?: Request::instance()->get([$params[$i] => isset($params[$i+1]) ? $params[$i+1] : '']);
        }

        // 设置url信息
        Request::instance()->get(Config::get('url_route_module'), $module);
        Request::instance()->get(Config::get('url_route_controller'), $controller);
        Request::instance()->get(Config::get('url_route_action'), $action);

        self::dispatch($module, $controller, $action);
    }

    /**
     * 请求调度分发控制中心
     * @param $module 模块名称
     * @param $controller 控制器名称
     * @param $action  请求方法名称
     */
    public static function dispatch($module, $controller, $action){
        $app_path = str_replace('/', '\\', APP_PATH . $module . '/controller/');
        $controllerClass = '\\'.Config::get('app_namespace').'\\'.$module.'\\controller\\' . ucwords($controller);

        Loader::load($app_path, $controller);
        // todo 错误处理
        if (!class_exists($controllerClass)) {
            exit($controllerClass . '控制器不存在');
        }
        if (!method_exists($controllerClass, $action)) {
            exit($controllerClass .'\\' . $action . '方法不存在');
        }

        $dispatch = new $controllerClass($module, $controller, $action);

        Request::instance()->module($module);
        Request::instance()->controller($controller);
        Request::instance()->action($action);

        // 测试使用
//        echo "excute: ".$controllerClass . '\\'. $action;
        $return = call_user_func_array(array($dispatch, $action), array(Request::instance()));
        if($return){
            exit_dump($return);
        }
//        exit("excute end;");
    }

}