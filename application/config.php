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

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用调试模式
    'app_debug'              => true,
    // 应用命名空间
    'app_namespace'              => 'app',
    // 应用目录名称
    'app_filename'              => 'application',
    // 是否启用框架系统函数
    'extra_framework_file'  => true,
    // 扩展函数文件
    'extra_file_list'        => ['common'],
    // 扩展配置文件
    'extra_config_list'        => ['extra_config'],

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'index',
    // 模块列表
    'module_list'             => ['index', 'admin'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',

    // +----------------------------------------------------------------------
    // | 视图设置
    // +----------------------------------------------------------------------

    // 视图后缀
    'view_suffix'  => 'html',
    // 视图全局替换变量
    'view_global'  => [
        '__PUBLIC__' => 'public',
        '__CSS__' =>  'public/static/css',
    ],

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // url路由模式
    // 1  =>  index.php?md=module&c=controller&m=method&param=value...
    // 2  =>  index.php?s=module/controller/method/param/value...
    // 3  =>  index.php/module/controller/method?param=value...
    // 4  =>  index.php/module/controller/method/param/value...
    // 分割   分隔符号代替/   比如 - * $ .
    'url_route_mode'       => 1,
    // 模块参数
    'url_route_module' => 'm',
    // 控制器参数
    'url_route_controller' => 'c',
    // 方法参数
    'url_route_action' => 'a',

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 'ape',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '-',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // 是否开启路由
    'url_route_on'           => true,
    // 路由配置文件（支持配置多个）, application目录下, 如果你的是application/route/index.php => 'route/index'
    'route_config_file'      => ['route'],
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                  => [
        // 驱动方式,目前只支持文件
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存后缀
        'suffix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 锁设置
    // +----------------------------------------------------------------------

    'lock'                  => [
        // 驱动方式,File,Redis(需要配置连接redis)
        'type'   => 'File',
        // 文件锁保存目录
        'path'   => LOCK_PATH,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'ape',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => 'ape',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

];