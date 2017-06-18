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

define('APE_VERSION', '1.0.0');
// 程序运行开始时的时间
define('APE_START_TIME', microtime(true));
// 程序运行开始时的内存
define('APE_START_MEM', memory_get_usage());

define('EXT', '.php');
define('DS', DIRECTORY_SEPARATOR);

// apephp框架根目录
defined('APE_PATH') or define('APE_PATH', __DIR__ . DS);
// apephp框架核心目录
define('LIB_PATH', APE_PATH . 'library' . DS);
define('CORE_PATH', LIB_PATH);
// 应用程序根目录
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
// 项目根目录
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
// 扩展应用程序根目录
defined('EXTEND_PATH') or define('EXTEND_PATH', ROOT_PATH . 'extend' . DS);
// 配置文件目录
defined('CONF_PATH') or define('CONF_PATH', APP_PATH);
// 配置文件后缀
defined('CONF_EXT') or define('CONF_EXT', EXT);

// 运行时根目录
defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);
// 日志输出根目录
defined('LOG_PATH') or define('LOG_PATH', RUNTIME_PATH . 'log' . DS);
// 缓存根目录
defined('CACHE_PATH') or define('CACHE_PATH', RUNTIME_PATH . 'cache' . DS);
// 文件锁根目录
defined('LOCK_PATH') or define('LOCK_PATH', RUNTIME_PATH . 'lock' . DS);
// 缓存视图根目录
defined('CACHE_VIEW_PATH') or define('CACHE_VIEW_PATH', CACHE_PATH . 'view' . DS);
// 临时文件根目录
defined('TEMP_PATH') or define('TEMP_PATH', RUNTIME_PATH . 'temp' . DS);

// 创建runtime目录
if(!file_exists(RUNTIME_PATH)) mkdir(RUNTIME_PATH, 0777);
// 创建日志目录
if(!file_exists(LOG_PATH)) mkdir(LOG_PATH, 0777);
// 创建日志目录
if(!file_exists(CACHE_PATH)) mkdir(CACHE_PATH, 0777);
// 创建文件锁目录
if(!file_exists(LOCK_PATH)) mkdir(LOCK_PATH, 0777);
// 创建缓存视图目录
if(!file_exists(CACHE_VIEW_PATH)) mkdir(CACHE_VIEW_PATH, 0777);
// 创建临时文件目录
if(!file_exists(TEMP_PATH)) mkdir(TEMP_PATH, 0777);

// 环境常量
define('IS_CLI', PHP_SAPI == 'cli');
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);

// 全局配置常量
define('CONFIG', 'config');
define('DATABASE', 'db');
define('ROUTE', 'route');

// 载入Loader类
require CORE_PATH . 'Loader.php';

// 注册自动加载
\ape\Loader::register();

