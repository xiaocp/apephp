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

class Response{

    // 原始数据
    protected $data;

    // 当前的contentType
    protected $contentType = 'text/html';

    // 字符集
    protected $charset = 'utf-8';

    //状态
    protected $code = 200;

    // 输出参数
    protected $options = [];
    // header参数
    protected $header = [];

    protected $content = null;

    /**
     * 页面输出类型
     * @param string $contentType 输出类型
     * @param string $charset     输出编码
     * @return $this
     */
    public function contentType($contentType, $charset = 'utf-8')
    {
        $this->header['Content-Type'] = $contentType . '; charset=' . $charset;
        return $this;
    }

}