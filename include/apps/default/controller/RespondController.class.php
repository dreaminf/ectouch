<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：RespondController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTOUCH 支付应答控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class RespondController extends CommonController
{

    protected $data = '';

    protected $code = '';

    public function __construct()
    {
        $this->code = I('get.code');
        $this->data = $_POST;
    }
    
    // 发送
    public function index()
    {
        $file = ADDONS_PATH . 'payment/' . $this->code . '.php';
        if (file_exists($file)) {
            require_once ($file);
            $payObj = new $this->code();
            if (isset($this->data['notify_data'])) {
                $this->notify($this->data);
            } else {
                $This->sync($this->data);
            }
        }
    }

    protected function sync($data = array())
    {
        $payObj->sync($data);
    }

    protected function notify($data = array())
    {
        $payObj->sync($data);
    }
}