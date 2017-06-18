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

class Url{

    private static $_default_module;
    private static $_default_controller;
    private static $_default_action;

    /**
     * 分隔符
     * @var string
     */
    protected static $depr = '/';

    /**
     * 开始
     */
    public static function build($url = '', $vars = '', $suffix = false, $domain = false){
        // 获取url路由模式
        $url_route_mode = Config::get('url_route_mode');

        // 获取分隔符
        self::$depr = Config::get('pathinfo_depr');

        // 获取默认控制器...
        self::$_default_module = Config::get('default_module');
        self::$_default_controller = Config::get('default_controller');
        self::$_default_action = Config::get('default_action');

        $c = substr_count($url, '/');
        if($c > 2){
            // todo 错误处理
        }

        // 解析URL
        if (0 !== strpos($url, '/')) {
            $info = parse_url($url);
            if (isset($info['fragment'])) {
                // 解析锚点
                $anchor = $info['fragment'];
                if (false !== strpos($anchor, '?')) {
                    // 解析参数
                    list($anchor, $info['query']) = explode('?', $anchor, 2);
                }
                if (false !== strpos($anchor, '@')) {
                    // 解析域名
                    list($anchor, $domain) = explode('@', $anchor, 2);
                }
            } elseif (strpos($url, '@') && false === strpos($url, '\\')) {
                // 解析域名
                list($url, $domain) = explode('@', $url, 2);
            }
        }

        if (is_string($vars)) {
            // aaa=1&bbb=2 转换成数组
            isset($info['query']) && $vars .= $info['query'];
            parse_str($vars, $vars);
        } else if(is_array($vars)){
            if(isset($info['query'])){
                parse_str($info['query'], $query);
                $vars = array_merge($vars, $query);
            }
        } else if(is_null($vars)){
            isset($info['query']) && parse_str($info['query'], $vars);
        }

        $module =& self::$_default_module;
        $controller =& self::$_default_controller;
        $action =& self::$_default_action;

        switch ($c){
            // 访问 module
            case 0:
                $url && $module = $url;
                break;
            // 访问 module/controller
            case 1:
                list($req_module, $req_controller) = explode('/', $url);
                $req_module && $module = $req_module;
                $req_controller && $controller = $req_controller;
                break;
            // 访问 module/controller/action
            case 2:
                list($req_module, $req_controller, $req_action) = explode('/', $url);
                $req_module && $module = $req_module;
                $req_controller && $controller = $req_controller;
                $req_action && $action = $req_action;
                break;
            default:
                // todo 错误处理
                break;
        }

        $base_url = [
            'module' => $module,
            'controller' => $controller,
            'action' => $action
        ];

        switch ($url_route_mode){
            // 传统模式 index.php?md=module&c=controller&m=method&param=value...
            case 1:
                $url = self::tradition($base_url, $vars, $suffix);
                break;
            // 兼容模式 index.php?s=module/controller/method/param/value...
            case 2:
                $url = self::pathinfo($base_url, $vars, $suffix);
                break;
            // module/controller/method?param=value...
            case 3:
                $url = self::cool($base_url, $vars, $suffix);
                break;
            // module/controller/method/param/value...
            case 4:
                $url = self::mode($base_url, $vars, $suffix);
                break;
            default:
                $url = self::tradition($base_url, $vars, $suffix);
                break;
        }
        return $domain ? $domain . $url : $url;
    }

    private static function tradition($url = [], $vars = '', $suffix = false){
        // 获取配置的参数名称
        $md = Config::get('url_route_module');
        $c = Config::get('url_route_controller');
        $m = Config::get('url_route_action');

        $mc = [
            $md => $url['module'],
            $c => $url['controller'],
            $m => $url['action']
        ];
        if(is_array($vars) && !empty($vars))
            $vars = array_merge($mc, $vars);
        else
            $vars = $mc;

        return Request::instance()->root() . '?' . http_build_query($vars);
    }

    private static function pathinfo($url = [], $vars = [], $suffix = false){

        $var_pathinfo = Config::get('var_pathinfo');
        $url = array_values($url);
        $url = implode(self::$depr, $url);

        $param = '';
        foreach ($vars as $k => $v){
            $param .= $k . self::$depr . $v . self::$depr;
        }
        $param = substr($param, 0, -1);

        $urls = $url . ($param ? self::$depr . $param : '');

        return Request::instance()->root() . '?'. $var_pathinfo . '=' . $urls ;
    }

    private static function cool($url = [], $vars = [], $suffix = false){
        $url = array_values($url);
        $url = implode(self::$depr, $url);

        $param = '';
        foreach ($vars as $k => $v){
            $param .= $k . '=' . $v . '&';
        }
        $param = substr($param, 0, -1);

        $urls = $url . ($param ? '?' . $param : '');
        return $urls;
    }

    private static function mode($url = [], $vars = [], $suffix = false){
        $url = array_values($url);
        $url = implode(self::$depr, $url);

        $param = '';
        foreach ($vars as $k => $v){
            $param .= $k . self::$depr . $v . self::$depr;
        }
        $param = substr($param, 0, -1);

        $urls = $url . self::$depr . ($param ? $param : '');
        return $urls;
    }

    // 直接解析URL地址
    protected static function parseUrl($url, &$domain)
    {

    }

    // 解析URL后缀
    protected static function parseSuffix($suffix)
    {
        if ($suffix) {
            $suffix = true === $suffix ? Config::get('url_html_suffix') : $suffix;
            if ($pos = strpos($suffix, '|')) {
                $suffix = substr($suffix, 0, $pos);
            }
        }
        return (empty($suffix) || 0 === strpos($suffix, '.')) ? $suffix : '.' . $suffix;
    }

}