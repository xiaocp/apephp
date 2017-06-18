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

use ape\exception\ClassNotFoundException;

class App{

    /**
     * @var bool 是否初始化过
     */
    protected static $init = false;
    /**
     * @var string 应用类库命名空间
     */
    public static $namespace = 'app';

    public static $_config = [];
    public static $_db = [];
    public static $_route = [];

    // debug打印日志
    public static $debug = false;

    private function __construct(){
        // 加载惯例配置文件
        Config::set(include APP_PATH . 'config' . EXT);
        Config::set(include APP_PATH . 'database' . EXT, DATABASE);
        // 加载扩展配置文件
        $extra_config_list = Config::get('extra_config_list');
        foreach ($extra_config_list as $item)
            Config::set(include APP_PATH . $item . EXT);

        // 加载路由配置文件
        $route_config_file = Config::get('route_config_file');
        foreach ($route_config_file as $item)
            Config::set(include APP_PATH . $item . EXT, ROUTE);

        $config = Config::get();

        !isset($config) ?: self::$_config = $config;
        !isset($config[DATABASE]) ?: self::$_db = $config[DATABASE];
        !isset($config[ROUTE]) ?: self::$_route = $config[ROUTE];
    }

    // 运行程序
    public static function run(){
        // 加载application
        self::initCommon();
        Error::register();
        Session::init();
        Cookie::init();
        spl_autoload_register('\\ape\\App::loadClass');
        self::setReporting();
        self::removeMagicQuotes();
        // 路由开始
        Route::start();
    }

    /**
     * 初始化应用
     */
    public static function initCommon(){
        if (empty(self::$init)) {
            // 初始化应用
            $config       = self::init();
            self::$namespace = self::$_config['app_namespace'];
            self::$debug = self::$_config['app_debug'];

            // 启用框架函数
            Config::get('extra_framework_file') == true && Loader::load(APE_PATH . 'helper' . EXT);
            // 添加扩展函数文件
            foreach (Config::get('extra_file_list') as $class)
                Loader::load(APP_PATH . $class . EXT);

            self::$init = true;
        }
    }

    /**
     * 初始化应用
     * @access public
     * @return array
     */
    private static function init()
    {
        // 加载惯例配置文件
        Config::set(include APP_PATH . 'config' . EXT);
        Config::set(include APP_PATH . 'database' . EXT, DATABASE);

        // 加载扩展配置文件
        $extra_config_list = Config::get('extra_config_list');
        foreach ($extra_config_list as $item)
            Config::set(include APP_PATH . $item . EXT);

        // 加载路由配置文件
        $route_config_file = Config::get('route_config_file');
        foreach ($route_config_file as $item)
            Config::set(include APP_PATH . $item . EXT, ROUTE);

        $config = Config::get();
        Db::$config = $config[DATABASE];

        !isset($config) ?: self::$_config = $config;
        !isset($config[DATABASE]) ?: self::$_db = $config[DATABASE];
        !isset($config[ROUTE]) ?: self::$_route = $config[ROUTE];

        return $config;
    }

    // 检测开发环境
    public static function setReporting()
    {
        if (self::$_config['app_debug'] === true) {
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors','Off');
            ini_set('log_errors', 'On');
        }
    }

    // 检测敏感字符并删除
    public static function removeMagicQuotes()
    {
        if (get_magic_quotes_gpc()) {
            $_GET = isset($_GET) ? self::stripSlashesDeep($_GET ) : '';
            $_POST = isset($_POST) ? self::stripSlashesDeep($_POST ) : '';
            $_COOKIE = isset($_COOKIE) ? self::stripSlashesDeep($_COOKIE) : '';
            $_SESSION = isset($_SESSION) ? self::stripSlashesDeep($_SESSION) : '';
        }
    }

    // 删除敏感字符
    public static function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map(array('App', 'stripSlashesDeep'), $value) : stripslashes($value);
        return $value;
    }

    // 自动加载类
    public static function loadClass($class){

        $class = str_replace('\\', '/', $class);
        $first_space = substr($class,0, strpos($class,'/'));
        // 判断是否application
        if($first_space == Config::get('app_namespace')){ // 加载 application
            $path = substr($class, strpos($class,'/') + 1);
            $app_class = str_replace('/', '\\',APP_PATH . $path . EXT);

            if (file_exists($app_class)) {
                // 加载框架核心类
                include $app_class;
            }else {
                // 异常处理,classNotFound
                exit(sprintf('Class %s not found.', $class));
                throw new ClassNotFoundException(sprintf('Class %s not found.', $class), $class);
            }
        }else{  // 加载extend
            $app_class = EXTEND_PATH . $class . EXT;
            $app_class = str_replace('/', '\\',$app_class);
            if (file_exists($app_class)) {
                // 加载框架核心类
                include $app_class;
            }else {
                // 异常处理,classNotFound
                throw new ClassNotFoundException(sprintf('Class %s not found.', $class), $class);
            }
        }
    }

}