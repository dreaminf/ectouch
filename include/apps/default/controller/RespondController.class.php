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

class RespondController extends CommonController {

    protected $data;

    public function __construct() {
        $this->data = '';
        //todo
    }

    //发送
    public function index() {
        
        if(isset($this->data['notify_data'])){
          $this->notify($this->data);
        }else{
          $This->sync($this->data)
        }
    }
    
    protected function sync($data = array()){
        $this->display('respond');
    }
    
    protected function notify($data = array()){
    
    }
}