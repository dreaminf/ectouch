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
		// 验证登录
		$this->check_login();
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
    public function crowd_like() {
		$this->type = I('request.type') ? intval(I('request.type')) : 1 ;
        $like = model('Mycrowd')->like_list($this->user_id, $this->type);//获取关注众筹
		$this->assign('like', $like);
		$this->assign('type', $this->type);
        $this->display('crowd/raise_follow.html');
    }
	
    
	
	/**
     * 未登录验证
     */
    private function check_login() {
        // 不需要登录的操作或自己验证是否登录（如ajax处理）的方法
        $without = array(
            'login',
            'register',
            'get_password_phone',
            'get_password_email',
            'get_password_question',
            'pwd_question_name',
            'send_pwd_email',
            'edit_password',
            'check_answer',
            'logout',
            'clear_histroy',
            'add_collection',
            'third_login',
            'bind',
            'unsetsession'
        );
        // 未登录处理
        if (empty($_SESSION['user_id']) && !in_array($this->action, $without)) {
            $url = __HOST__ . $_SERVER['REQUEST_URI'];
            $this->redirect(url('login', array(
                'referer' => urlencode($url)
            )));
            exit();
        }

        // 已经登录，不能访问的方法
        $deny = array(
            'login',
            'register'
        );
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0 && in_array($this->action, $deny)) {
            $this->redirect(url('index/index'));
            exit();
        }
    }
	
	
	
	
	
	
	
	
	
	
	
   

}
