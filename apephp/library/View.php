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

class View {

    protected static $instance;

    protected $variables = [];
    protected $_module;
    protected $_controller;
    protected $_action;

    // 缓存设置
    protected $_cache = false;
    // 缓存时间
    protected $_cache_time = 0;

    private function __construct3($module, $controller, $action)
    {
        $this->_module = $module;
        $this->_controller = $controller;
        $this->_action = $action;
    }

    private function __construct1($agr1)
    {
        
    }

    private function __construct2($agr1, $agr2)
    {

    }

    private function __construct0()
    {
        $this->_module = Request::instance()->module();
        $this->_controller = Request::instance()->controller();
        $this->_action = Request::instance()->action();
    }

    public function __construct(){
        $count = func_num_args();
        switch ($count){
            case 0:
                $this->__construct0();
                break;
            case 1:
                $this->__construct1(func_get_arg(0));
                break;
            case 2:
                $this->__construct2(func_get_arg(0), func_get_arg(1));
                break;
            case 3:
                $this->__construct3(func_get_arg(0), func_get_arg(1), func_get_arg(2));
                break;
        }
    }

    /**
     * 初始化
     * @access public
     * @return \ape\View
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // 分配变量, $name is null get all
    public function assign($name, $value)
    {
        if(!$name) return $this->variables;
        $this->variables[$name] = $value;
        return $this;
    }

    // 渲染显示
    public function view($view = '')
    {
        extract($this->variables);

        // 获取模块信息
        if($at_index = strpos($view, '@')){
            list($module, $view) = explode('@', $view, 2);
        } else
            $module = $this->_module;

        // 默认获取视图文件
        if(!$view)
            $view_path = ROOT_PATH . Config::get('app_filename') . '/' . $module . '/view/' . $this->_controller . '/' . $this->_action . '.' . Config::get('view_suffix');
        else
            $view_path = ROOT_PATH . Config::get('app_filename') . '/' . $module .'/view/' . $view . '.' . Config::get('view_suffix');

        // 加载视图
        if (file_exists($view_path)) {
            ob_clean();
            header("X-Powered-By:ApePHP" . APE_VERSION);
            eval('?>'.$this->compile($view_path));
        } else {
            // todo 错误处理
            exit_dump("View - error");
        }
    }

    // 获取类中未声明变量
    public function &__get($key) {
        if (array_key_exists($key, $this->variables)) {
            return $this->variables[$key];
        }
    }

    // 设置类中未声明变量
    public function __set($key, $val) {
        $this->variables[$key] = $val;
    }

    /**
     * 设置视图缓存时间
     * @param int $extension
     * @return $this 支持连贯操作
     */
    public function setCache($extension = 0){
        if($extension > 0 ){
            $this->_cache = true;
            $this->_cache_time = $extension;
        }
        return $this;
    }

    // 转换
    private function compile($file)
    {
        // 引入缓存
        $this->_cache && $cache = Cache::instance();

        if ($this->_cache && $cache->has($file)) {
            $file_str = $cache->get($file);
        } else {
            $keys = [
                '{if %%}' => '<?php if (\1): ?>',
                '{elseif %%}' => '<?php ; elseif (\1): ?>',
                '{for %%}' => '<?php for (\1): ?>',
                '{foreach %%}' => '<?php foreach (\1): ?>',
                '{while %%}' => '<?php while (\1): ?>',
                '{/if}' => '<?php endif; ?>',
                '{/for}' => '<?php endfor; ?>',
                '{/foreach}' => '<?php endforeach; ?>',
                '{/while}' => '<?php endwhile; ?>',
                '{else}' => '<?php ; else: ?>',
                '{continue}' => '<?php continue; ?>',
                '{break}' => '<?php break; ?>',
                '{$%% = %%}' => '<?php $\1 = \2; ?>',
                '{$%%++}' => '<?php $\1++; ?>',
                '{$%%--}' => '<?php $\1--; ?>',
                '{$%%|default=%%}' => '<?php echo isset($\1) ? $\1 : \2; ?>',
                '{$%%}' => '<?php echo $\1; ?>',  // 变量
                '{comment}' => '<?php /*',
                '{/comment}' => '*/ ?>',
                '{/*}' => '<?php /*',
                '{*/}' => '*/ ?>',
                '{:%%}' => '<?php echo \1; ?>', // 函数
            ];

            $file_str  = file_get_contents($file);
            // 获取当前模板block
            $result = preg_match_all('/{block name=(.*?)}\s*?(.*?)\s*?{\/block}/', $file_str, $blocks);
            if($result && $blocks)
                foreach ($blocks[1] as $key => $val)
                    $block_array[str_replace(['\'', '"'], '', $val)] = $blocks[2][$key];
            // 获取父模板
            $result = preg_match('/{extend name=(.+)}/', $file_str, $extends);
            $result && $extend_path = str_replace(['\'', '"'], '', $extends[1]);
            if(isset($extend_path)){
                if($index320 = strpos($extend_path, '@')){
                    // 获取模块名称
                    $module320 = strstr($extend_path, '@', true);
                    $file320 = trim(substr($extend_path, $index320 + 1)) . Config::get('view_suffix');  // 获取视图配置后缀信息
                    $path320 = APP_PATH . $module320 . '/view/' . $file320;
                }else{
                    $file320 = trim($extend_path) . '.html';                  // 严密处理去除空格
                    $path320 = APP_PATH . ''.$this->_module.'/view/' . $file320;   // 加载当前模块
                }
                $extend_content = file_get_contents($path320);
                $result = preg_match_all('/{block name=(.*?)}\s*?.*?\s*?{\/block}/', $extend_content, $extend_blocks);
                if($result && $extend_blocks && isset($block_array))
                    foreach ($extend_blocks[1] as $key => $val){
                        $t = str_replace(['\'', '"'], '', $val);
                        $t_replace = '';
                        if(isset($block_array[$t]))
                            $t_replace = $block_array[$t];
                        $extend_content = str_replace($extend_blocks[0][$key], $t_replace, $extend_content);
                    }
                $file_str = $extend_content;
            }

            // include标签处理
            $result = preg_match_all('/{include file=(.*?)}/', $file_str, $includes);
            if($result)
                foreach ($includes[1] as $key => $val){
                    $include_path = str_replace(['\'', '"'], '', $val);
                    if($include_path) {
                        if ($index320 = strpos($include_path, '@')) {
                            // 获取模块名称
                            $module320 = strstr($include_path, '@', true);
                            $file320 = trim(substr($include_path, $index320 + 1)) . Config::get('view_suffix');  // 获取视图配置后缀信息
                            $path320 = APP_PATH . $module320 . '/view/' . $file320;
                        } else {
                            $file320 = trim($include_path) . '.html';                  // 严密处理去除空格
                            $path320 = APP_PATH . '' . $this->_module . '/view/' . $file320;   // 加载当前模块
                        }
                        $include_content = file_get_contents($path320);
                        $file_str = str_replace($includes[0][$key], $include_content, $file_str);
                    }
                }

            $vars = Config::get('view_global');
            foreach ($vars as $key => $value){
                $vars_patterns[] = $key;
                $vars_replace[] = Request::instance()->baseUrl() . $value;
            }
            isset($vars_patterns) && isset($vars_replace) && $file_str = str_replace($vars_patterns, $vars_replace, $file_str);

            foreach ($keys as $key => $val) {
                $patterns[] = '#' . str_replace('%%', '(.+)', preg_quote($key, '#')) . '#U';
                $replace[] = $val;
            }
            isset($patterns) && isset($replace) && $file_str = preg_replace($patterns, $replace, $file_str);
            // 引入缓存
            $this->_cache && $cache->set($file, $file_str, $this->_cache_time);
        }
        return $file_str;
    }

}