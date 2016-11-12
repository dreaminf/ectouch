<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：ApiController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTouch接口控制器
 * 调用说明：url('api/index', array('openid'=>$openid, 'title'=>$title, 'msg'=>$msg, 'url'=>$url));
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class ApiController extends CommonController
{
    // 会员ID
    public $user_id = 0;
    // 消息标识
    public $code = '';
    // 消息内容
    public $pushData = '';
    // 消息链接
    public $url = '';
    // 微信类对象
    private $weObj = '';

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->user_id = I('get.user_id',0,'intval');
        $this->code = I('get.code','','trim');
        $this->pushData = I('get.pushData','','trim');
		$this->url = I('get.url','');
		$this->url = $this->url ? base64_decode(urldecode($this->url)) : '';
    }

    /**
     * 接口方法
     */
    public function index()
    {
        $this->pushData = stripslashes(urldecode($this->pushData));
        //转换成数组
        $this->pushData = unserialize($this->pushData);
        pushTemplate($this->code, $this->pushData, $this->url, $this->user_id);
    }

    public function qrcode(){
        $userid = I('userid', '0', 'intval');
        echo call_user_func(array('WechatController', 'rec_qrcode'), $userid);
    }
}
