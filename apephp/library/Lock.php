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

class Lock{

    //文件锁存放路径
    private static $path = LOCK_PATH;
    //文件句柄
    private static $fp = null;
    //锁文件
    private static $lockFile = 'ape';

    /**
     * 加锁
     * @return bool
     */
    private static function lock(){
        self::$fp = fopen(self::$lockFile,'w');
        if(self::$fp === false){
            return false;
        }
        return flock(self::$fp, LOCK_EX | LOCK_NB);//获取独占锁
    }

    /**
     * 解锁
     */
    private static function unlock(){
        if(self::$fp !== false){
            @flock(self::$fp, LOCK_UN);
            clearstatcache();
        }
        fclose(self::$fp);
        //@unlink(self::$lockFile);
    }

    /**
     * 获取锁
     * @param $lock_id
     * @param $run
     * @param $fail
     */
    public static function run($lock_id, $run, $fail){
        self::$lockFile = self::$path . md5($lock_id) . '.lock';
        if(self::lock()){
            $run();
            self::unlock();
        } else{
            $fail();
            fclose(self::$fp);
        }
    }

}