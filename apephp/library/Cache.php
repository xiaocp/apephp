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

class Cache {

    // 全局单一实例
    protected static $instance;
    // 存储路径
    protected $_cachepath = CACHE_PATH;
    // 前缀
    protected $_prefix = '';
    // 后缀
    protected $_suffix = '.cache';
    // 缓存时间, 0 为永久
    protected $_extension = 0;

    public function __construct($options = []){
        // 获取默认配置信息
        if($t_path = Config::get('cache.path')) $this->_cachepath = $t_path;
        if($t_prefix = Config::get('cache.prefix')) $this->_prefix = $t_prefix;
        if($t_suffix = Config::get('cache.suffix')) $this->_suffix = $t_suffix;
        if($t_extension = Config::get('cache.expire')) $this->_extension = $t_extension;
        // 设置 path, prefix, suffix, extension
        !array_key_exists('path', $options) ?: $this->_cachepath = $options['path'];
        !array_key_exists('prefix', $options) ?: $this->_prefix = $options['prefix'];
        !array_key_exists('suffix', $options) ?: $this->_suffix = $options['suffix'];
        !array_key_exists('extension', $options) ?: $this->_extension = $options['extension'];
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return \ape\Cache
     */
    public static function instance($options = []){
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 设置缓存
     * @param $key key值
     * @param $data 数据
     * @param int $expiration 过期时间(单位:秒)
     * @return $this
     */
    public function set($key, $data, $expiration = 0) {
        if($expiration == 0 && isset($this->_extension))
            $expiration = $this->_extension;
        $storeData = [
            'time' => time(),
            'expire' => $expiration,
            'data' => serialize($data)
        ];
        $dataArray = $this->loadCache($key);
        if (true === is_array($dataArray)) {
            $dataArray[$key] = $storeData;
        } else {
            $dataArray = [$key => $storeData];
        }
        $cacheData = json_encode($dataArray);
        file_put_contents($this->getCacheDir($key), $cacheData);
        return $this;
    }

    /**
     * 获取缓存
     * @param $key
     * @return mixed|null
     */
    public function get($key) {
        $cachedData = $this->loadCache($key);
        if($cachedData === false)
            return null;
        // 是否过期
        $expiration = $cachedData[$key]['expire'];
        if($expiration != 0 && time() >= $cachedData[$key]['time'] + $expiration){
            $this->delete($key);
            return null;
        }
        if (!isset($cachedData[$key]['data'])) {
            $this->delete($key);
            return null;
        }
        return unserialize($cachedData[$key]['data']);
    }

    /**
     * 清除缓存
     */
    public function clear(){
        $op = dir($this->_cachepath);
        while(false != ($item = $op->read())) {
            if($item == '.' || $item == '..') {
                continue;
            }
            if(is_dir($op->path.'/'.$item)) {
                //clear($op->path.'/'.$item);
                //rmdir($op->path.'/'.$item);
            } else {
                @unlink($op->path.'/'.$item);
            }

        }
    }

    /**
     * 删除缓存
     * @param $key
     */
    public function delete($key){
        $path = $this->getCacheDir($key);
        @unlink($path);
    }

    /**
     * 检查缓存 key 是否存在
     * @param $key
     * @return bool
     */
    public function has($key){
        if (false != $this->loadCache($key)) {
            $cachedData = $this->loadCache($key);
            // 是否过期
            $expiration = $cachedData[$key]['expire'];
            if($expiration != 0 && time() >= $cachedData[$key]['time'] + $expiration){
                $this->delete($key);
                return null;
            }
            return isset($cachedData[$key]['data']);
        }
        return false;
    }

    /**
     * 获取缓存文件内容
     * @return bool|mixed
     */
    private function loadCache($key) {
        if (true === file_exists($this->getCacheDir($key))) {
            $file = file_get_contents($this->getCacheDir($key));
            return json_decode($file, true);
        } else {
            return false;
        }
    }

    /**
     * 获取缓存的路径，包含文件名
     * @return string
     */
    public function getCacheDir($key) {
        if (true === $this->checkCacheDir()) {
            //$filename = $this->_cachename;
            //$filename = preg_replace('/[^0-9a-z\.\_\-]/i', '', strtolower($filename));
            return $this->_cachepath . $this->_prefix . $this->getHash($key) . $this->_suffix;
        }
    }

    /**
     * 检查缓存的路径
     * @return bool
     * @throws \Exception
     */
    private function checkCacheDir() {
        if (!is_dir($this->_cachepath) && !mkdir($this->_cachepath, 0775, true)) {
            throw new \Exception('Unable to create cache directory ' . $this->_cachepath);
        } elseif (!is_readable($this->_cachepath) || !is_writable($this->_cachepath)) {
            if (!chmod($this->_cachepath, 0775)) {
                throw new \Exception($this->_cachepath . ' must be readable and writeable');
            }
        }
        return true;
    }

    /**
     * key的hsah值
     * @param $key
     * @return string
     */
    private function getHash($key) {
        return sha1($key);
    }

}