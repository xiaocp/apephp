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

class  Error{

    protected static $type = [
        E_ERROR => 'error',
        E_WARNING => 'warning',
        E_PARSE => 'prase',
        E_NOTICE => 'notice'
    ];

    /**
     * 注册异常处理
     * @return void
     */
    public static function register()
    {
        error_reporting(E_ALL);
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }

    /**
     * Error Handler
     * @param  integer $errno   错误编号
     * @param  integer $errstr  详细错误信息
     * @param  string  $errfile 出错的文件
     * @param  integer $errline 出错行号
     * @param array    $errcontext
     * @throws ErrorException
     */
    public static function appError($errno, $errstr, $errfile = '', $errline = 0, $errcontext = [])
    {
        // debug
        if(App::$debug){
            isset(self::$type[$errno]) ? $type = self::$type[$errno] : $type = 'debug';
            $msg = '{'.$type.'}:'. $errstr .' On ' . $errfile .'line:' . $errline;
            Log::instance([
                'key' => 'log',
                'type' => $type,
                'msg' => $msg
            ])->save();
        }
        exit($msg);
    }

    public static function appShutdown(){
        // 获取错误信息
        $error_msg = error_get_last();
        $type = '';
        isset(self::$type[$error_msg['type']]) && $type = self::$type[$error_msg['type']];
        $msg = '{'.$type.'}:'. $error_msg['message'] .' On ' . $error_msg['file'] .'line:' . $error_msg['line'];
        if($type)
            Log::instance([
                'key' => 'log',
                'type' => $type,
                'msg' =>$msg
            ])->save();
    }

    public static function appException($e){

    }

    public function show(){
        //todo 错误页面
    }

}