<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：IndexController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTouch我的众筹控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class MycrowdController extends CommonController {
	 /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
		$this->user_id = $_SESSION['user_id'];
        // 用户信息
        $info = model('ClipsBase')->get_user_default($this->user_id);
        // 显示第三方API的头像
        if(isset($_SESSION['avatar'])){
            $info['avatar'] = $_SESSION['avatar'];
        }
		$this->assign('info', $info);
    }
	
    /**
     * 众筹项目列表信息
     */
    public function index() {
		
        $recommend = model('Mycrowd')->recom_list();//获取推荐众筹
		$this->assign('recommend', $recommend);
        $this->display('crowd/raise_user.html');
    }
	
	/**
     * 关注众筹项目列表信息
     */
    public function my_like() {
		
        //$recommend = model('Mycrowd')->recom_list();//获取推荐众筹
		//$this->assign('recommend', $recommend);
        $this->display('crowd/raise_follow.html');
    }
	
    
	
	
	
	
	
	
	
	
	
	
	
	
	
   

}
