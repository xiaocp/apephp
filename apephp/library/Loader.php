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

class Loader {

    /* 路径映射 */
    public static $vendorMap = [
        'ape' => CORE_PATH
    ];

    protected static $instance = [];
    // 类名映射
    protected static $map = [];

    // 命名空间别名
    protected static $namespaceAlias = [];

    public function __construct(){

    }

    /**
     * 手动加载类
     * @param $file
     * @param null $class
     */
    public static function load($file, $class = null){
        if(!$class) {
            if (file_exists($file)) {
                self::includeFile($file);
            }else{
                // todo 加载文件失败
            }
        }
        $file = $file . $class . EXT;
        $file = strtr($file, '\\', DIRECTORY_SEPARATOR);
        if (file_exists($file)) {
            self::includeFile($file);
        }else{
            // todo 加载文件失败
        }
    }

    // 注册自动加载机制
    public static function register(){
        spl_autoload_register('\\ape\\Loader::autoload'); // 注册自动加载
    }

    /**
     * 自动加载器
     * @param $class
     */
    public static function autoload($class)
    {
        $file = self::findFile($class);
        if (file_exists($file)) {
            self::includeFile($file);
        }
    }

    /**
     * 解析文件路径
     * @param $class
     * @return string
     */
    private static function findFile($class)
    {
        $vendor = substr($class, 0, strpos($class, '\\')); // 顶级命名空间
        if(!isset(self::$vendorMap[$vendor]))
            return '';
        $vendorDir = self::$vendorMap[$vendor]; // 文件基目录
        $filePath = substr($class, strlen($vendor)) . '.php'; // 文件相对路径
        return strtr($vendorDir . $filePath, '\\', DIRECTORY_SEPARATOR); // 文件标准路径
    }

    /**
     * 引入文件
     * @param $file
     */
    private static function includeFile($file)
    {
        if (is_file($file)) {
            include $file;
        }
    }

}