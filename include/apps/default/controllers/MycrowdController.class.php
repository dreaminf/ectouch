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
    public function crowd_like() {
		$this->type = I('request.type') ? intval(I('request.type')) : 1 ;
        $like = model('Mycrowd')->like_list($this->user_id, $this->type);//获取关注众筹
		$this->assign('like', $like);
		$this->assign('type', $this->type);
        $this->display('crowd/raise_follow.html');
    }
	
	/**
     * 我的支持众筹项目列表信息
     */
    public function crowd_buy() {
		$this->type = I('request.type') ? intval(I('request.type')) : 1 ;
        $buy_list = model('Mycrowd')->crowd_buy_list($this->user_id, $this->type);//获取我的支持众筹项目
		$this->assign('buy_list', $buy_list);
		$this->assign('type', $this->type);
        $this->display('crowd/raise_support.html');
    }
    
	
	/**
     * 关余众筹
     */
    public function crowd_articlecat() {
        $sql = 'SELECT cat_id, cat_name' .
            ' FROM ' .$this->model->pre. 'article_cat ' .
            ' WHERE cat_type = 1 AND parent_id = 0' .
            ' ORDER BY sort_order ASC';
        $data = $this->model->query($sql);
        foreach($data as $key=>$vo){
            $data[$key]['url'] = url('crowd_art_list', array('id'=>$vo['cat_id']));
        }
        $this->assign('data', $data); //文章分类树
        $this->display('crowd/raise_help.html');
    }
	
	
	/**
     * 关余众筹详细
     */
    public function crowd_art_list() {
		$id = I('request.id') ? intval(I('request.id')) : 0 ;
        $sql = 'SELECT title, 	description' .
            ' FROM ' .$this->model->pre. 'article ' .
            " WHERE is_open = 1 AND cat_id = '$id'" ;
        $data = $this->model->query($sql);
        $this->assign('data', $data);
        $this->display('crowd/raise_problem.html');
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
   

}
