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

class Controller{

    protected $request;
    protected $_module;
    protected $_controller;
    protected $_action;
    protected $view;

    // 构造函数，初始化属性，并实例化对应模型
    public function __construct($module, $controller, $action)
    {
        !is_null($this->request) ?: $this->request = Request::instance();
        $this->_module = $module;
        $this->_controller = $controller;
        $this->_action = $action;
        $this->view = new View($module, $controller, $action);
    }

    // 分配变量
    public function assign($name, $value)
    {
        $this->view->assign($name, $value);
    }

    // 渲染视图
    public function display($view = '')
    {
        $this->view->view($view);
    }

}