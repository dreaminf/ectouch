<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：CommonControoller.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：公共控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class CommonController extends BaseController
{

    protected static $user = NULL;

    protected static $sess = NULL;

    protected static $view = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->ecshop_init();
        // 微信oauth处理
        if(class_exists('WechatController')){
            if (method_exists('WechatController', 'do_oauth')) {
                call_user_func(array('WechatController', 'do_oauth'));
                /*DRP_START*/
                $this->drp();
                /*DRP_END*/
                $this->wechatJsSdk();
            }
        }

        /* 语言包 */
        $this->assign('lang', L());
        /* 页面标题 */
        $page_info = get_page_title();
        self::$view->assign('page_title', $page_info['title']);
        self::$view->assign('meta_keywords', C('shop_keywords'));
        self::$view->assign('meta_description', C('shop_desc'));
        C('show_asynclist', 1);
        /* 模板赋值 */
        assign_template();
    }

    static function user()
    {
        return self::$user;
    }

    static function sess()
    {
        return self::$sess;
    }

    static function view()
    {
        return self::$view;
    }

    protected function fetch($filename, $cache_id = '')
    {
        return self::$view->fetch($filename, $cache_id);
    }

    protected function assign($tpl_var, $value = '')
    {
        self::$view->assign($tpl_var, $value);
    }

    protected function display($tpl = '', $cache_id = '', $return = false)
    {
        self::$view->display($tpl, $cache_id);
    }

    protected function ecshop_init()
    {
        header('Cache-control: private');
        header('Content-type: text/html; charset=utf-8');
        
        $shop_closed = C('shop_closed');
        if (! empty($shop_closed)) {
            $close_comment = C('close_comment');
            $close_comment = empty($close_comment) ? 'closed.':$close_comment;
            exit('<h1 style="font-size: 5rem;text-align: center;margin-top: 40%;">'.$close_comment.'</h1>');
        }
        //NULL
        // 初始化session
        self::$sess = new EcsSession(self::$db, self::$ecs->table('sessions'), self::$ecs->table('sessions_data'), C('COOKIE_PREFIX').'touch_id');
        define('SESS_ID', self::$sess->get_session_id());
        
        // 创建 Smarty 对象
        self::$view = new EcsTemplate();
        self::$view->cache_lifetime = C('cache_time');
        self::$view->template_dir = ROOT_PATH . 'themes/' . C('template');
        self::$view->cache_dir = ROOT_PATH . 'data/attached/caches';
        self::$view->compile_dir = ROOT_PATH . 'data/attached/compiled';
        
        if ((DEBUG_MODE & 2) == 2) {
            self::$view->direct_output = true;
            self::$view->force_compile = true;
        } else {
            self::$view->direct_output = false;
            self::$view->force_compile = false;
        }
        self::$view->caching = true;
        
        // 会员信息
        self::$user = init_users();
        if (empty($_SESSION['user_id'])) {
            if (self::$user->get_cookie()) {
                // 如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券
                if ($_SESSION['user_id'] > 0 && ! isset($_SESSION['user_money'])) {
                    model('Users')->update_user_info();
                }
            } else {
                $_SESSION['user_id'] = 0;
                $_SESSION['user_name'] = '';
                $_SESSION['email'] = '';
                $_SESSION['user_rank'] = 0;
                $_SESSION['discount'] = 1.00;
            }
        }
        
        // 判断是否支持gzip模式
        if (gzip_enabled()) {
            ob_start('ob_gzhandler');
        }
        
        // 设置推荐会员
        if (isset($_GET['u'])) {
            set_affiliate();
        }
        
        // session不存在，检查cookie
        if (! empty($_COOKIE['ECS']['user_id']) && ! empty($_COOKIE['ECS']['password'])) {
            // 找到cookie,验证信息
            $where['user_id'] = $_COOKIE['ECS']['user_id'];
            $where['password'] = $_COOKIE['ECS']['password'];
            $row = $this->model->table('users')
                ->field('user_id, user_name, password')
                ->where($where)
                ->find();
            if ($row) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_name'] = $row['user_name'];
                model('Users')->update_user_info();
            } else {
                // 没有找到这个记录
                $time = time() - 3600;
                setcookie("ECS[user_id]", '', $time, '/');
                setcookie("ECS[password]", '', $time, '/');
            }
        }
        
        // search 关键词
        $search_keywords = C('search_keywords');
        if (!empty($search_keywords) && is_string($search_keywords)) {
            $keywords = explode(',', $search_keywords);
            $this->assign('hot_search_keywords', $keywords);
        }
        // 模板替换
        defined('__TPL__') or define('__TPL__', __ROOT__ . '/themes/' . C('template'));
        $stylename = C('stylename');
        if (! empty($stylename)) {
            $this->assign('ecs_css_path', __ROOT__ . '/themes/' . C('template') . '/css/style_' . C('stylename') . '.css');
        } else {
            $this->assign('ecs_css_path', __ROOT__ . '/themes/' . C('template') . '/css/style.css');
        }

        // 设置parent_id
        session('parent_id',$_SESSION['user_id'] ? 0 : $_GET['u'] ? $_GET['u'] : 0);
    }
    
    /*DRP_START*/
    /*
     * 分销店铺信息
     */
    private function drp(){

        if($_GET['drp_id'] > 0){
            $drp_info = model('Sale')->get_drp($_GET['drp_id'],'1');
            if($drp_info['open'] == 1){
                $drp_info['cat_id'] = substr($drp_info['cat_id'], 0, -1);
                $_SESSION['drp_shop'] = $drp_info;
                model('Sale')->drp_visiter($_GET['drp_id']);

            }
        }elseif($_SESSION['user_id'] && !$_SESSION['drp_shop']){
            $drp_info = model('Sale')->get_drp($_SESSION['user_id']);
            if($drp_info['open'] == 1){
                $drp_info['cat_id'] = substr($drp_info['cat_id'], 0, -1);
                $_SESSION['drp_shop'] = $drp_info;

            }else{
                $parent_id = $this->model->table('users')->field('parent_id')->where("user_id=".$_SESSION['user_id'])->getOne();
                if($parent_id){
                    $drp_info = model('Sale')->get_drp($parent_id);
                    if($drp_info['open'] == 1) {
                        $drp_info['cat_id'] = substr($drp_info['cat_id'], 0, -1);
                        $_SESSION['drp_shop'] = $drp_info;
                    }
                }

            }
        }

        // 判断访问控制器
        if(CONTROLLER_NAME == 'Index' && ACTION_NAME=='index' && I('type') != 'share' && $_SESSION['drp_shop']['open'] == 1){
            redirect(url('store/index'));
        }
    }
    /*DRP_END*/

    /*
     * 微信jsSDK
     */
    public function wechatJsSdk(){
        $wxinfo   = model('Base')->model->table('wechat')->field('token, appid, appsecret, status')->find();
        
        $appid    = $wxinfo['appid'];
        $secret   = $wxinfo['appsecret'];

        //分销商信息
        $drp_id   = isset($_SESSION['drp_shop']['drp_id']) ? $_SESSION['drp_shop']['drp_id'] : 0;

        //微信店信息
        $drp_shop = $_SESSION['drp_shop'];
        $wx_title = C('shop_name').$drp_shop['shop_name'];
        $wx_desc  = C('shop_desc');
        $wx_url   = __URL__ . '/index.php?u=' . $_SESSION['user_id'] . '&drp_id='.$drp_id;
        $wx_pic   = __URL__ . '/images/logo.png';

        //商品信息
        if(CONTROLLER_NAME == 'Goods' && isset($_GET['id'])){
            $goods_id = I('id', 0);
            $goods = model('Goods')->get_goods_info($goods_id);
            $wx_title = $goods['goods_name'];
            $wx_desc  = $goods['goods_name'];
            $wx_url   = __URL__ .'/index.php?c=goods&id='.$goods_id.'&u=' . $_SESSION['user_id'] . '&drp_id='.$drp_id;
            $wx_pic   = $goods['goods_thumb'];
        }
        $wx_url.='&type=share';
        //微信JS SDK
        $jssdk = new Jssdk($appid, $secret);
        $signPackage = $jssdk->GetSignPackage();

        $this->assign('wx_title', $wx_title);
        $this->assign('wx_desc', $wx_desc);
        $this->assign('wx_url', $wx_url);
        $this->assign('wx_pic', $wx_pic);

        $this->assign('appid', $signPackage["appId"]);
        $this->assign('timestamp', $signPackage["timestamp"]);
        $this->assign('noncestr', $signPackage["nonceStr"]);
        $this->assign('signature', $signPackage["signature"]);

        $output = $this->fetch('library/js_sdk.lbi');
        if ($wxinfo['status']) {
            $this->assign('wechat_js_sdk', $output);
        }
    }
}

class_alias('CommonController', 'ECTouch');
