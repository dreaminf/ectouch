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
	
	protected $user_id;
    protected $action;
    protected $back_act = '';
	
	 /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();		
		$this->user_id = $_SESSION['user_id'];
		$this->action = ACTION_NAME;
		// 验证登录
        $this->check_login();
        // 用户信息
        $info = model('ClipsBase')->get_user_default($this->user_id);
        // 显示第三方API的头像
        if(isset($_SESSION['avatar'])){
            $info['avatar'] = $_SESSION['avatar'];
        }
		
		// 如果是显示页面，对页面进行相应赋值
        assign_template();

		$this->assign('info', $info);
		$this->assign('action', $this->action);
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
     * 关余众筹详细order_list
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
	
	
	/**
     * 众筹订单
     */
    public function crowd_order() {

		$this->status = I('request.status') ? intval(I('request.status')) : 1 ;
		//dump($this->status);
		$pay = $this->status;
        $size = I(C('page_size'),10);
        $count = model('Mycrowd')->crowd_orders_num($this->user_id, $this->status);//获取订单数量
		//dump($count);
        $filter['page'] = '{page}';
        $offset = $this->pageLimit(url('crowd_order', $filter), $size);
        $offset_page = explode(',', $offset);
        $orders = model('Mycrowd')->crowd_user_orders($this->user_id, $pay, $offset_page[1], $offset_page[0]);
		if(!$orders){
			show_message('暂无内容');
		}
		//dump($orders);
        $this->assign('pay', $pay);
        $this->assign('title', L('order_list_lnk'));
        $this->assign('pager', $this->pageShow($count));
		$this->assign('status', $this->status);
        $this->assign('orders_list', $orders);
		
		
        $this->display('crowd/raise_order.html');
    }
	
	
	/**
     * 获取订单商品的评论列表 
    */
    public function crowd_comment_list() {
        
		
		
		
		$this->display('crowd/raise_user_evaluation.html');
    }
	
	
	/**
     * 获取订单商品的评论列表 
    */
    public function crowd_comment() {
        
		
		
		
		$this->display('crowd/raise_user_evaluation_info.html');
    }
	
	
	
	
	
	
	
	
	
	
	
	 /**
     * 取消订单
     */
    public function cancel_order() {
        $order_id = I('get.order_id', 0, 'intval');

        if (model('Users')->cancel_order($order_id, $this->user_id)) {
            $url = url('crowd_order');
            ecs_header("Location: $url\n");
            exit();
        } else {
            ECTouch::err()->show(L('order_list_lnk'), url('crowd_order'));
        }
    }
	
	private function check_login() {
        // 是否登陆
        if(empty($this->user_id)){
            $url = 'http://'.$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            redirect(url('user/login', array('referer' => urlencode($url)) ));
            exit();
        }
	
	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
   

}
