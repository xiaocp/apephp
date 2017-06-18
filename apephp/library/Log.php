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

/**
 * Class Log
 * @package ape
 * @method void log($msg) static
 * @method void error($msg) static
 * @method void info($msg) static
 * @method void sql($msg) static
 * @method void notice($msg) static
 * @method void alert($msg) static
 * @method void debug($msg) static
 */
class Log {
    const LOG    = 'log';
    const ERROR  = 'error';
    const INFO   = 'info';
    const SQL    = 'sql';
    const NOTICE = 'notice';
    const ALERT  = 'alert';
    const DEBUG  = 'debug';

    protected static $instance;
    // 日志信息
    protected static $log = [];
    // 配置参数
    protected static $config = [
        'time_format' => ' c ',
        'file_size'   => 2097152,
        'path'        => LOG_PATH,
    ];
    // 日志类型
    protected static $type = ['log', 'error', 'info', 'sql', 'notice', 'alert', 'debug'];
    // 当前日志授权key
    protected static $key = 'info';

    public function __construct($options = [])
    {
        // key  => $key
        // type => 类型
        // msg  => 信息
        !array_key_exists('key', $options) && isset($options['key']) && $options['key'] != '' ?: self::$key = $options['key'];
        !array_key_exists('type', $options) && in_array($options['type'], self::$type) && !array_key_exists('msg', $options) ?: self::$log[$options['type']] = $options['msg'];
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return \ape\Log
     */
    public static function instance($options = []){
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 当前日志记录的授权key
     * @param string $key 授权key
     * @return void
     */
    public static function key($key)
    {
        self::$key = $key;
    }

    // 保存日志
    public static function save(){
        foreach (self::$log as $k => $v){
            $log = "[$k][".date('Y-m-d H:i:s')."]:{$v}\n";
            // 保存路径
            $file = self::$config['path'] . md5(self::$key) . '.log';
            //检测日志文件大小，超过配置大小则备份日志文件重新生成
            if (is_file($file) && floor(self::$config['file_size']) <= filesize($file)) {
                rename($file, dirname($file) . DS . time() . '-' . basename($file));
            }
            error_log($log, 3, $file);
        }
    }

    /**
     * 记录调试信息
     * @param mixed  $msg  调试信息
     * @param string $type 信息类型
     * @return void
     */
    public static function record($msg, $type = 'log')
    {
        self::$log[$type] = $msg;
        if (IS_CLI) {
            // 命令行下面日志写入改进
            self::save();
        }
    }

    /**
     * 静态调用
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::$type)) {
            array_push($args, $method);
            return call_user_func_array('\\ape\\Log::record', $args);
        }
    }

}