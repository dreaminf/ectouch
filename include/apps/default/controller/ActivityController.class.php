<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：ActivityControoller.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：优惠活动控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class ActivityController extends CommonController {

    private $children = '';
    private $brand = '';
    private $goods = '';
    private $size = 10;
    private $page = 1;
    private $sort = 'last_update';
    private $order = 'ASC';

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 优惠活动 列表
     */
    public function index() {
        $this->parameter();
        $this->assign('page', $this->page);
        $this->assign('size', $this->size);
        $this->assign('sort', $this->sort);
        $this->assign('order', $this->order);
		$count = model('Activity')->get_activity_count();
        $this->pageLimit(url('index'), $this->size);
        $this->assign('pager', $this->pageShow($count));
        
        $list = model('Activity')->get_activity_info($this->size, $this->page);
        $this->assign('list', $list);
		
        $this->display('activity.dwt');
    }

    /**
     * 优惠活动 - 异步加载
     */
    public function asynclist() {
        // 开始工作
        $this->parameter();
        $asyn_last = intval(I('post.last')) + 1;
        $this->size = I('post.amount');
        $this->page = ($asyn_last > 0) ? ceil($asyn_last / $this->size) : 1;
        $list = model('Activity')->get_activity_info($this->size, $this->page);
        foreach ($list as $key => $activity) {
            $this->assign('activity', $activity);
            $sayList [] = array(
                'single_item' => ECTouch::view()->fetch('library/asynclist_info.lbi')
            );
        }
        die(json_encode($sayList));
        exit();
    }

    /**
     * 优惠活动 - 活动商品列表
     */
    public function goods_list() {
        $this->parameter();
        $id = intval(I('request.id'));
        $this->assign('id', $id);
        $this->assign('page', $this->page);
        $this->assign('size', $this->size);
        $this->assign('sort', $this->sort);
        $this->assign('order', $this->order);
        if (!$id) {
            $this->redirect(url('index'));
        }
        $res = $this->model->table('favourable_activity')->field()->where("act_id = '$id'")->order('sort_order ASC')->find();
        $list = array();

        if ($res['act_range'] != FAR_ALL && !empty($res['act_range_ext'])) {
            if ($res['act_range'] == FAR_CATEGORY) {
                $this->children = " cat_id " . db_create_in(get_children_cat($res['act_range_ext']));
            } elseif ($res['act_range'] == FAR_BRAND) {
                $this->brand = " g.brand_id " . db_create_in($res['act_range_ext']);
            } else {
                $this->goods = " g.goods_id " . db_create_in($res['act_range_ext']);
            }
        }
        $count = model('Activity')->category_get_count($this->children, $this->brand, $this->goods,$this->price_min, $this->price_max,$this->ext);
        $this->pageLimit(url('goods_list', array('id' => $id, 'brand' => $this->brand, 'sort' => $this->sort, 'order' => $this->order)), $this->size);
        $this->assign('pager', $this->pageShow($count));
        $goods_list = model('Activity')->category_get_goods($this->children, $this->brand, $this->goods, $this->size, $this->page, $this->sort, $this->order);
        $this->assign('goods_list', $goods_list);
        $this->display('activity_goods_list.dwt');
    }

    /**
     * 优惠活动 - 活动商品列表 -异步加载
     */
    public function asynclist_list() {
        $this->parameter();
        $id = intval(I('request.id'));
        if (!$id) {
            $this->redirect(url('index'));
        }
        $res = $this->model->table('favourable_activity')->field()->where("act_id = '$id'")->order('sort_order ASC')->find();
        $list = array();

        if ($res['act_range'] != FAR_ALL && !empty($res['act_range_ext'])) {
            if ($res['act_range'] == FAR_CATEGORY) {
                $this->children = " cat_id " . db_create_in(get_children_cat($res['act_range_ext']));
            } elseif ($res['act_range'] == FAR_BRAND) {
                $this->brand = "g.brand_id " . db_create_in($res['act_range_ext']);
            } else {
                $this->goods = "g.goods_id " . db_create_in($res['act_range_ext']);
            }
        }
        $this->assign('id', $id);
        $asyn_last = intval(I('post.last')) + 1;
        $this->size = I('post.amount');
        $this->page = ($asyn_last > 0) ? ceil($asyn_last / $this->size) : 1;
        $goodslist = model('Activity')->category_get_goods($this->children, $this->brand, $this->goods, $this->size, $this->page, $this->sort, $this->order);
        foreach ($goodslist as $key => $value) {
            $this->assign('act_goods', $value);
            $sayList [] = array(
                'single_item' => ECTouch::view()->fetch('library/asynclist_info.lbi')
            );
        }
        die(json_encode($sayList));
        exit();
    }

    /**
     * 处理参数便于搜索商品信息
     */
    private function parameter() {
        // 如果分类ID为0，则返回总分类页
        $page_size = C('page_size');
        $this->size = intval($page_size) > 0 ? intval($page_size) : 10;
        $this->page = I('request.page')? intval(I('request.page')) : 1;
        /* 排序、显示方式以及类型 */
        $default_display_type = C('show_order_type') == '0' ? 'list' : (C('show_order_type') == '1' ? 'grid' : 'album');
        $default_sort_order_method = C('sort_order_method') == '0' ? 'DESC' : 'ASC';
        $default_sort_order_type = C('sort_order_type') == '0' ? 'goods_id' : (C('sort_order_type') == '1' ? 'shop_price' : 'last_update');

        $this->assign('show_asynclist', C('show_asynclist'));
        $this->sort = (isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array(
                    'goods_id',
                    'shop_price',
                    'last_update',
                    'sales_volume',
                    'click_count'
                ))) ? trim($_REQUEST['sort']) : $default_sort_order_type; // 增加按人气、按销量排序 by wang
        $this->order = (isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array(
                    'ASC',
                    'DESC'
                ))) ? trim($_REQUEST['order']) : $default_sort_order_method;
        $display = (isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array(
                    'list',
                    'grid',
                    'album'
                ))) ? trim($_REQUEST['display']) : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
        $this->assign('display', $display);
        setcookie('ECS[display]', $display, gmtime() + 86400 * 7);
    }

    /**
     * 微信交流墙
     */
    public function wall_msg(){
        $wall_id = I('get.wall_id');
        if(empty($wall_id)){
            $this->redirect(url('index/index'));
        }

        //活动内容
        $wall = $this->model->table('wechat_wall')->field('id, name, logo, background, starttime, endtime, prize, content, support')->where(array('id'=>$wall_id, 'status'=>1))->find();

        //留言
        $sql = "SELECT u.nickname, u.headimg, m.content, m.addtime FROM ".$this->model->pre."wechat_wall_msg m LEFT JOIN ".$this->model->pre."wechat_wall_user u ON m.user_id = u.id WHERE m.status = 1 and u.wall_id = '$wall_id' ORDER BY m.addtime desc";
        $list = $this->model->query($sql);

        $this->assign('wall', $wall);
        $this->assign('list', $list);
        $this->display('wall/wall_msg.dwt');
    }

    /**
     * 微信端抽奖用户申请
     */
    public function wall_user_wechat(){
        if(IS_POST){
            $wall_id = I('post.wall_id');
            if(empty($wall_id)){
                show_message("请选择对应的活动");
            }
            $data['nickname'] = I('post.nickname');
            $data['headimg'] = I('post.headimg');
            $data['sex'] = I('post.sex');
            $data['wall_id'] = $wall_id;
            $data['addtime'] = time();
            $data['openid'] = $_SESSION['openid'];

            $this->model->table('wechat_wall_user')->data($data)->insert();
            $this->redirect(url('wall_msg_wechat', array('wall_id'=>$wall_id)));
            exit;
        }
        $wall_id = I('get.wall_id');
        if(empty($wall_id)){
            $this->redirect(url('index/index'));
        }
        //更改过头像跳到聊天页面
        $wechat_user = $this->model->table('wechat_wall_user')->where(array('openid'=>$_SESSION['openid']))->count();
        if($wechat_user > 0){
            $this->redirect(url('wall_msg_wechat', array('wall_id'=>$wall_id)));
        }

        $this->assign('user', $_SESSION['wechat_user']);
        $this->assign('wall_id', $wall_id);
        $this->display('wall/wall_user_wechat.dwt');
    }

    /**
     * 微信端留言页面
     */
    public function wall_msg_wechat(){
        if(IS_POST && IS_AJAX){
            $wall_id = I('post.wall_id');
            if(empty($wall_id)){
                exit(json_encode(array('code'=>1, 'errMsg'=>'请选择对应的活动')));
            }
            $data['user_id'] = I('post.user_id');
            $data['content'] = I('post.content');
            if(empty($data['user_id']) || empty($data['content'])){
                exit(json_encode(array('code'=>1, 'errMsg'=>'请先登录或者发表的内容不能为空')));
            }
            $data['addtime'] = time();

            $this->model->table('wechat_wall_msg')->data($data)->insert();
            //留言成功，跳转
            exit(json_encode(array('code'=>0)));
        }
        $wall_id = I('get.wall_id');
        if(empty($wall_id)){
            $this->redirect(url('index/index'));
        }
        $_SESSION['openid'] = 'oWbbLt4fDrg78mvacsfpvi9Juo4I';

        $wechat_user = $this->model->table('wechat_wall_user')->field('id')->where(array('openid'=>$_SESSION['openid']))->find();
        //聊天室人数
        $user_num = $this->model->table('wechat_wall_msg')->field("COUNT(DISTINCT user_id)")->getOne();

        //初始缓存
        $cache_key = md5('cache_0');
        $Eccache = new EcCache();
        $list = $Eccache->get($cache_key);
        if(!$list){
            $sql = "SELECT m.content, m.addtime, u.nickname, u.headimg, u.id FROM ".$this->model->pre."wechat_wall_msg m LEFT JOIN ".$this->model->pre."wechat_wall_user u ON m.user_id = u.id WHERE m.status = 0 ORDER BY addtime ASC LIMIT 0, 5";
            $data = $this->model->query($sql);
            $Eccache->set($cache_key, $data, 30);
            $list = $Eccache->get($cache_key);
        }


        $this->assign('list', $list);
        $this->assign('msg_count', count($list));
        $this->assign('user_num', $user_num);
        $this->assign('user', $wechat_user);
        $this->assign('wall_id', $wall_id);
        $this->display('wall/wall_msg_wechat.dwt');
    }

    /**
     * ajax请求留言
     */
    public function get_wall_msg(){
        if(IS_AJAX && IS_GET){
            $start = I('get.start', 5);
            $num = I('get.num', 5);
            if($start && $num){
                $Eccache = new EcCache();
                $cache_key = md5('cache_'.$start);
                $list = $Eccache->get($cache_key);
                if(!$list){
                    $sql = "SELECT m.content, m.addtime, u.nickname, u.headimg, u.id FROM ".$this->model->pre."wechat_wall_msg m LEFT JOIN ".$this->model->pre."wechat_wall_user u ON m.user_id = u.id WHERE m.status = 0 ORDER BY addtime ASC LIMIT ".$start.", ".$num;
                    $data = $this->model->query($sql);
                    $Eccache->set($cache_key, $data, 30);
                    $list = $Eccache->get($cache_key);
                }
                $result = array('code'=>0, 'data'=>$list);
                exit(json_encode($result));
            }
        }
        else{
            $result = array('code'=>1, 'errMsg'=>'请求不合法');
            exit(json_encode($result));
        }
    }

    /**
     * 微信头像墙
     */
    public function wall_user(){
        $wall_id = I('get.wall_id');
        if(empty($wall_id)){
            $this->redirect(url('index/index'));
        }
        //活动内容
        $wall = $this->model->table('wechat_wall')->field('id, name, logo, background, starttime, endtime, prize, content, support')->where(array('id'=>$wall_id, 'status'=>1))->find();

        //用户
        $list = $this->model->table('wechat_wall_user')->field('nickname, headimg')->where(array('wall_id'=>$wall_id, 'status'=>1))->order('addtime desc')->select();

        $this->assign('wall', $wall);
        $this->assign('list', $list);
        $this->display('wall/wall_user.dwt');
    }

    /**
     * 抽奖
     */
    public function wall_prize(){
        $wall_id = I('get.wall_id');
        if(empty($wall_id)){
            $this->redirect(url('index/index'));
        }

        $this->display();
    }

}
