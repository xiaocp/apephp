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

use ape\db\Medoo;
use ape\db\MySql;
use PDO;
use PDOException;

class Db{

    //  数据库连接实例
    private static $instance = null;
    private static $newInstance = [];
    private static $id = 1;
    private static $medoo = null;
    public static $config = [];

    /**
     * 获取一个pdo实例
     * @param $host
     * @param $username
     * @param $password
     * @param $database
     * @param $charset
     * @return PDO
     */
    public static function connect($host, $username, $password, $database, $charset){
        try {
            $dsn = sprintf("mysql:host=%s;dbname=%s;charset=%s", $host, $database, $charset);
            $option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
            $pdo = new PDO($dsn, $username, $password, $option);
            $pdo->exec('SET character_set_connection='.$charset.', character_set_results='.$charset.', character_set_client=binary');
        } catch (PDOException $e) {
            // todo 异常处理
            exit($e->getMessage());
        }
        return $pdo;
    }

    /**
     * 获取一个新的mysql对象
     * @param $id
     * @param $config
     * @return MySql|null
     */
    public static function newInstance($id = null, $config = null){
        // 获取默认的id对象
        if(is_null($id) && is_null($config))
            return isset(self::$newInstance[self::$id]) ? self::$newInstance[self::$id] : null;
        // 设置默认的id对象
        elseif(is_array($id) && $config == null){
            self::$newInstance[self::$id] = new MySql(null, $id);
            return isset(self::$newInstance[self::$id]) ? self::$newInstance[self::$id] : null;
        } elseif($id != null && $config == null)
            return isset(self::$newInstance[$id]) ? self::$newInstance[$id] : null;
        else{
            self::$newInstance[$id] = new MySql(null, $config);
            return isset(self::$newInstance[$id]) ? self::$newInstance[$id] : null;
        }
    }

    /**
     * 获取Mysql实例
     * @return MySql|null
     */
    public static function instance(){
        if(self::$instance == null)
            self::$instance = new MySql();
        return self::$instance;
    }

    /**
     * 获取一个Medoo数据库操作实例
     * @return Medoo|null
     */
    public static function medoo(){
        if (self::$medoo === null) {
            self::$medoo = new Medoo([
                // 必须配置项
                'database_type' => self::$config['type'],
                'database_name' => self::$config['database'],
                'server' => self::$config['hostname'],
                'username' => self::$config['username'],
                'password' => self::$config['password'],
                'charset' => self::$config['charset'],

                // 可选参数
                'port' => self::$config['hostport'],
                // 可选，定义表的前缀
                'prefix' => self::$config['prefix'],

                // 连接参数扩展, 更多参考 http://www.php.net/manual/en/pdo.setattribute.php
                'option' => [
                    PDO::ATTR_CASE => PDO::CASE_NATURAL
                ]
            ]);
        }
        return self::$medoo;
    }

}