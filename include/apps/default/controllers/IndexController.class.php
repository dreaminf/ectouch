<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：IndexController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTouch首页控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class IndexController extends CommonController {

    /**
     * 首页信息
     */
    public function index() {
        $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-subscribe' . $_SESSION['subscribe'] . '-' . C('lang')));
        if (!ECTouch::view()->is_cached('team/index.html', $cache_id))
        {
            // 自定义导航栏
            $navigator = model('Common')->get_navigator();
            $this->assign('navigator', $navigator['middle']);
            $this->assign('best_goods', model('Index')->goods_list('best', C('page_size')));
            $this->assign('new_goods', model('Index')->goods_list('new', C('page_size')));
            $this->assign('hot_goods', model('Index')->goods_list('hot', C('page_size')));
            // 调用促销商品
            $this->assign('promotion_goods', model('Index')->goods_list('promotion', C('page_size')));
            //首页推荐分类
            $cat_rec = model('Index')->get_recommend_res(10,4);
            $this->assign('cat_best', $cat_rec[1]);
            $this->assign('cat_new', $cat_rec[2]);
            $this->assign('cat_hot', $cat_rec[3]);
            // 促销活动
            $this->assign('promotion_info', model('GoodsBase')->get_promotion_info());
            // 团购商品
            $this->assign('group_buy_goods', model('Groupbuy')->group_buy_list(C('page_size'),1,'goods_id','ASC'));
            // 获取分类
            $this->assign('categories', model('CategoryBase')->get_categories_tree());
            // 获取品牌
            $this->assign('brand_list', model('Brand')->get_brands($app = 'brand', C('page_size'), 1));
            // 分类下的文章
            $this->assign('cat_articles', model('Article')->assign_articles(1,5)); // 1 是文章分类id ,5 是文章显示数量
            //获取推荐众筹
            $recommend = model('Mycrowd')->recom_list();
            $this->assign('recommend', $recommend);

            // 验证首页下单提示轮播是否开启
            $sql = 'SELECT value FROM ' . $this->model->pre . 'shop_config'." WHERE code = 'virtual_order'";
            $is_open = $this->model->getRow($sql);
            if($is_open['value'] == 1){
                $this->assign('is_open', 1);
            }
            // 获取频道
            $this->assign('team_categories', model('Index')->team_categories_tree());
            // 微信JSSDK分享
            $share_data = array(
                'title' => C('shop_name'),
                'desc' => C('shop_desc'),
                'link' => '',
                'img' => '',
            );
            $this->assign('share_data', $this->get_wechat_share_content($share_data));
        }
        // 关注按钮 是否显示
        $this->assign('subscribe', $_SESSION['subscribe']);
        $this->display('team/index.html', $cache_id);

    }

    /**
     * ajax获取商品
     */
    public function ajax_goods() {
        if (IS_AJAX) {
            $type = I('get.type');
            $id = I('get.id');
            $start = $_POST['last'];
            $limit = $_POST['amount'];
            $goods_list = model('Index')->goods_list($type,$id, $limit, $start);
            $list = array();
            // 商品列表
            if ($goods_list) {
                foreach ($goods_list as $key => $value) {
                    $value['iteration'] = $key + 1;
                    $this->assign('goods', $value);
                    $list [] = array(
                        'single_item' => ECTouch::view()->fetch('library/asynclist_index.lbi')
                    );
                }
            }
            echo json_encode($list);
            exit();
        } else {
            $this->redirect(url('index'));
        }
    }

    /**
     * ajax获取频道商品
     */
    public function tean_ajax_goods() {
        if (IS_AJAX) {
            //$type = I('get.type');
            $id = I('get.id');
            $start = $_POST['last'];
            $limit = $_POST['amount'];
            $goods_list = model('Index')->team_goods_list($id, $limit, $start);
            $list = array();
            // 热卖商品
            if ($goods_list) {
                foreach ($goods_list as $key => $value) {
                    $value['iteration'] = $key + 1;
                    $this->assign('goods', $value);
                    $list [] = array(
                        'single_item' => ECTouch::view()->fetch('library/asynclist_index.lbi')
                    );
                }
            }
            echo json_encode($list);
            exit();
        } else {
            $this->redirect(url('index'));
        }
    }

    /**
     * 首页频道信息
     */
    public function team_category() {
        $id = isset($_REQUEST ['id']) ? intval($_REQUEST ['id']) : 0;
        // 获取频道
        $this->assign('team_categories', model('Index')->team_categories_tree());
        // 获取子频道
        $this->assign('team_child', model('Index')->team_get_child_tree($id));
        // 获取频道名称
        $name = $this->model->table('team_category')->where("id=" . $id)->field('name')->getone();
        // 验证首页下单提示轮播是否开启
        $sql = 'SELECT value FROM ' . $this->model->pre . 'shop_config'." WHERE code = 'virtual_order'";
        $is_open = $this->model->getRow($sql);
        if($is_open['value'] == 1){
            $this->assign('is_open', 1);
        }
        $this->assign('id', $id);
        $this->assign('title', $name);
        $this->display('team/index_fresh.html');
    }

    //首页下单提示轮播
    public function virtual_order(){
         //格式化返回数组
        $arr = array(
            'err_msg' => '',
            'name' => '',
            'avatar' => '',
            'seconds' => ''
        );
        $sql = 'SELECT value FROM ' . $this->model->pre . 'shop_config'." WHERE code = 'virtual_order'";
        $is_open = $this->model->getRow($sql);
        if($is_open['value'] == 1){
            //随机用户
            if($_SESSION['user_id']!=0){
                $sql = 'SELECT user_name, user_id FROM ' . $this->model->pre . 'users'." WHERE user_id <> ".$_SESSION['user_id']." ORDER BY rand() LIMIT 1";
            }
            else{
                $ip = $this->getIP();
                $sql = 'SELECT user_name, user_id FROM ' . $this->model->pre . 'users'." WHERE last_ip <> '$ip' ORDER BY rand() LIMIT 1";
            }
            $user = $this->model->getRow($sql);
            if($user){
            $arr['name'] = $user['user_name'];
            $arr['avatar'] = 'themes/default/images/member-photo-img2.jpeg';


            $wechat_user = $this->model->table('wechat_user')->where("ect_uid=" . $user['user_id'])->field('nickname,headimgurl')->find();
            if (!empty($wechat_user)) {
                $arr['name'] = $wechat_user['nickname'];
                $arr['avatar'] = $wechat_user['headimgurl'];
            }

            //随机秒数
            $arr['seconds']=rand(1,8)."秒前";
            }else{
                $arr ['err_no'] = 1;
                //$arr='';
            }

        }else{
            $arr ['err_no'] = 1;
            //$arr='';
        }

        die(json_encode($arr));
    }

    private function getIP()    {
        static $realip;
        if (isset($_SERVER)){
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")){
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }
        return $realip;
    }


}
