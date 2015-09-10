<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：SaleController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTouch用户中心
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class SaleController extends CommonController {

    protected $user_id;
    protected $action;
    protected $back_act = '';
    private $sale = null;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        // 属性赋值
        $this->user_id = $_SESSION['user_id'];
        $this->action = ACTION_NAME;
        // 验证登录
        $this->check_login();
        // 用户信息
        $info = model('ClipsBase')->get_user_default($this->user_id);
        // 如果是显示页面，对页面进行相应赋值
        assign_template();
        $this->assign('action', $this->action);
        $this->assign('info', $info);
        // 获取店铺信息
        $this->sale = $this->model->table('drp_shop')->where(array("user_id"=>$_SESSION['user_id']))->find();

        $without = array('sale_set', 'sale_set_category', 'sale_set_end');
        if(!$this->sale && !in_array($this->action, $without)){
            redirect(url('sale/sale_set'));
        }
        $this->assign('user_id',session('user_id'));
    }


    /**
     * 会员中心欢迎页
     */
    public function index() {
        $shop = $this->model->table('drp_shop')->where(array('user_id'=>$_SESSION['user_id']))->field('create_time,shop_name')->find();
        $sale['time'] = date('Y-m-d H:i:s',$shop['create_time']);
        $sale['shop_name'] = $shop['shop_name'];
        $this->assign('sale',$sale);
        // 账户余额
        $sale_money = model('Sale')->saleMoney();
        $this->assign('sale_money',$sale_money);
        // 佣金总额
        $sale_money = model('Sale')->saleMoney();
        $this->assign('sale_money',$sale_money ? $sale_money : '0.00');
        // 今日收入
        $sale_money_today = model('Sale')->saleMoney_today();
        $this->assign('sale_money_today',$sale_money_today ? $sale_money_today : '0.00');

        $this->assign('title', L('sale'));
        $this->display('sale.dwt');
    }

    /**
     * 我的商品
     */
    public function my_goods(){
        if(IS_POST){
            $cateArr = I('cate');
            $cat_id = '';
            if($cateArr){
                foreach($cateArr as $key=>$val){
                    $cat_id.=$val.',';
                }
            }else{
                show_message(L('sale_cate_not_empty'));
            }
            $data['cat_id'] = $cat_id;
            $where['user_id'] = $_SESSION['user_id'];
            $this->model->table('drp_shop')->data($data)->where($where)->update();
            show_message(L('success'),'分销中心',url('sale/index'));
        }
        $cat_id = $this->model->table('drp_shop')->field("cat_id")->where(array("user_id"=>$_SESSION['user_id']))->getOne();
        $catArr = explode(',',$cat_id);
        if($catArr){
            unset($catArr[(count($catArr)-1)]);
        }
        $category = $this->model->table('category')->field("cat_id,cat_name")->where(array("parent_id"=>0))->select();
        if($category){
            foreach($category as $key=>$val){
                $category[$key]['profit1'] = $this->model->table('drp_profit')->field("profit1")->where(array("cate_id"=>$val['cat_id']))->getOne();
                $category[$key]['profit1'] = $category[$key]['profit1'] ? $category[$key]['profit1'] : 0;
                $category[$key]['profit2'] = $this->model->table('drp_profit')->field("profit2")->where(array("cate_id"=>$val['cat_id']))->getOne();
                $category[$key]['profit2'] = $category[$key]['profit2'] ? $category[$key]['profit2'] : 0;
                $category[$key]['profit3'] = $this->model->table('drp_profit')->field("profit3")->where(array("cate_id"=>$val['cat_id']))->getOne();
                $category[$key]['profit3'] = $category[$key]['profit3'] ? $category[$key]['profit3'] : 0;
                if(in_array($val['cat_id'],$catArr)){
                    $category[$key]['is_select'] = 1;
                }
            }
        }

        $this->assign('category',$category);
        $this->assign('title', '我的商品');
        $this->display('sale_my_goods.dwt');
    }

    /**
     * 佣金管理
     */
    public function account_detail() {
        $this->assign('key',I('key'));
        // 获取剩余余额
        $surplus_amount = model('Sale')->saleMoney($this->user_id);
        if (empty($surplus_amount)) {
            $surplus_amount = 0;
        }

        $size = I(C('page_size'), 5);
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $where = 'user_id = ' . $this->user_id . ' AND user_money <> 0 ';
        $sql = "select COUNT(*) as count from {pre}drp_log where $where";
        $count = $this->model->query($sql);
        $count = $count['0']['count'];
        $this->pageLimit(url('sale/account_detail'), $size);
        $this->assign('pager', $this->pageShow($count));
        $account_detail = model('Sale')->get_sale_log($this->user_id, $size, ($page-1)*$size);
        $this->assign('title', L('add_surplus_log'));
        $this->assign('surplus_amount', price_format($surplus_amount, false));
        $this->assign('account_log', $account_detail);
        $this->display('sale_account_detail.dwt');
    }

    /**
     *  会员申请提现
     */
    public function account_raply(){
        $list = $this->model->table('drp_bank')->where("user_id=".$this->user_id)->select();
        $this->assign('list',$list);
        // 获取剩余余额
        $surplus_amount = model('Sale')->saleMoney($this->user_id);
        if (empty($surplus_amount)) {
            $surplus_amount = 0;
        }
        $this->assign('surplus_amount', price_format($surplus_amount, false));
        $this->assign('title', L('label_user_surplus'));
        $this->display('sale_account_raply.dwt');
    }

    /**
     *  对会员佣金申请的处理
     */
    public function act_account()
    {
        $bank = I('bank') ? I('bank') : 0;
        if($bank == 0){
            show_message('请选择提现的银行卡');
        }
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        if (!is_numeric($amount)){
            show_message(L('amount_gt_zero'));
        }elseif ($amount <= 0)
        {
            show_message(L('amount_gt_zero'));
        }

        /* 变量初始化 */
        $surplus = array(
            'user_id'      => $this->user_id,
            'rec_id'       => !empty($_POST['rec_id'])      ? intval($_POST['rec_id'])       : 0,
            'process_type' => isset($_POST['surplus_type']) ? intval($_POST['surplus_type']) : 0,
            'payment_id'   => isset($_POST['payment_id'])   ? intval($_POST['payment_id'])   : 0,
            'user_note'    => isset($_POST['user_note'])    ? trim($_POST['user_note'])      : '',
            'amount'       => $amount
        );
        /* 判断是否有足够的余额的进行退款的操作 */
        $sur_amount =  model('Sale')->saleMoney($this->user_id);
        if ($amount > $sur_amount)
        {
            show_message('佣金金额不足', L('back_page_up'), '', 'info');
        }
        //插入会员账目明细
        $surplus['payment'] = '';
        $surplus['rec_id']  =  model('ClipsBase')->insert_user_account($surplus, $amount);

        /* 如果成功提交 */
        if ($surplus['rec_id'] > 0)
        {
            $bank_info = $this->model->table('drp_bank')->where("user_id=".$this->user_id ." and id=".$bank)->select();
            $bank_info = $bank_info['0'];
            /* 插入帐户变动记录 */
            $account_log = array(
                'user_id'       => $this->user_id,
                'user_money'    => '-'.$amount,
                'change_time'   => gmtime(),
                'change_desc'   => isset($_POST['user_note'])    ? trim($_POST['user_note'])      : '',
                'bank_info'   => "银行名称：".$bank_info['bank_name']." 帐号：".$bank_info['bank_card'],
            );

            $this->model->table('drp_log')
                ->data($account_log)
                ->insert();

            /* 更新用户信息 */
            $sql = "UPDATE {pre}drp_shop" .
                " SET money = money - ('$amount')" .
                " WHERE user_id = '$this->user_id' LIMIT 1";
            $this->model->query($sql);

            $content = L('surplus_appl_submit');
            show_message($content, L('back_account_log'), url('sale/account_detail'), 'info');
        }
        else
        {
            $content = L('process_false');
            show_message($content, L('back_page_up'), '', 'info');
        }


    }

    /**
     * 我的佣金
     */
    public function my_commission(){
        $saleMoney =  model('Sale')->saleMoney();
        $this->assign('saleMoney',$saleMoney);
        // 未分成
        // 本店销售佣金
        $sale_money = model('sale')->get_shop_sale_money($this->user_id);
        $this->assign('sale_no_money',$sale_money);
        // 分成佣金
        $separate_money = model('sale')->get_user_separate_money($this->user_id);
        $this->assign('separate_no_money1',$separate_money['money1']);
        $this->assign('separate_no_money2',$separate_money['money2']);
        $this->assign('separate_no_money3',$separate_money['money3']);
        $this->assign('separate_no_money',$sale_money+$separate_money['money1']+$separate_money['money2']+$separate_money['money3']);

        // 以分成
        // 本店销售佣金
        $sale_money = model('sale')->get_shop_sale_money($this->user_id,1);
        $this->assign('sale_money',$sale_money);
        // 分成佣金
        $separate_money = model('sale')->get_user_separate_money($this->user_id,1);
        $this->assign('separate_money1',$separate_money['money1']);
        $this->assign('separate_money2',$separate_money['money2']);
        $this->assign('separate_money3',$separate_money['money3']);
        $this->assign('separate_money',$sale_money+$separate_money['money1']+$separate_money['money2']+$separate_money['money3']);
        $this->assign('title','我的佣金');
        $this->display('sale_my_commission.dwt');
    }

    /**
     * 朋友圈推广
     */
    public function share(){
        $this->assign('mobile_qr', call_user_func(array('WechatController', 'rec_qrcode'), session('user_name'),session('user_id')));
        $this->assign('title',L('share'));
        $this->display('sale_share.dwt');
    }

    /**
     * 推广二维码
     */
    public function spread(){
        // 销售二维码
        $this->assign('mobile_qr', call_user_func(array('WechatController', 'rec_qrcode'), session('user_name'),session('user_id')));

        $this->assign('title',L('spread'));
        $this->display('sale_spread.dwt');
    }

    /**
     * 店铺二维码
     */
    public function store(){
        $filename  = './data/attached/drp1';
        if(!file_exists($filename)){
            mkdir($filename);
        }
        $mobile_qr = './data/attached/drp/drp_'.$_SESSION['user_id'].'.png';
        if(!file_exists($drp_info['mobile_qr'])){
            // 二维码
            $url = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?drp_id='.$_SESSION['user_id'];
            // 纠错级别：L、M、Q、H
            $errorCorrectionLevel = 'L';
            // 点的大小：1到10
            $matrixPointSize = 8;
            @QRcode::png($url, $mobile_qr, $errorCorrectionLevel, $matrixPointSize, 2);
        }
        $this->assign('mobile_qr', $mobile_qr);
        $this->assign('title',L('store'));
        $this->display('sale_store.dwt');
    }

    public function order_list(){
        $user_id = I('user_id') > 0 ? I('user_id') : $_SESSION['user_id'];
        $drp_id = $this->model->table('drp_shop')->field('id')->where("user_id=".$user_id)->getOne();
        if(!$drp_id){
            show_message('此用户尚未开店，无法查看订单');
        }
        $size = I(C('page_size'), 5);
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $where = 'drp_id = '.$drp_id;
        $count = $this->model->table('order_info')->field('COUNT(*)')->where($where)->getOne();
        $this->pageLimit(url('sale/order_list'), $size);
        $this->assign('pager', $this->pageShow($count));
        $orders = model('Sale')->get_sale_orders($where ,  $size, ($page-1)*$size,$user_id);

        if($orders){
            foreach($orders as $key=>$val){
                foreach($val['goods'] as $k=>$v){
                    $orders[$key]['goods'][$k]['profit'] = model('Sale')->get_drp_profit($v['goods_id']);
                    $orders[$key]['goods'][$k]['profit_money'] =$v['touch_sale']*$orders[$key]['goods'][$k]['profit'] /100;
                    $orders[$key]['sum']+=$orders[$key]['goods'][$k]['profit_money'];
                }

            }
        }
        $this->assign('orders_list', $orders);
        $this->assign('title', L('order_list'));
        $this->display('sale_order_list.dwt');
    }
    /**
     * 获取全部未付款订单
     */
    public function order_list_wfk() {
        $user_id = I('user_id') > 0 ? I('user_id') : $_SESSION['user_id'];
        $where = 'parent_id = '.$user_id;
        $where=$where." and pay_status = '".PS_UNPAYED."'";
        $size = I(C('page_size'), 20);
        $sql = "select count(*) as count from {pre}order_info where $where";
        $count = $this->model->getOne($sql);
        $orders = get_sale_orders($where , 10 , 0 ,$user_id);
        foreach ($orders as $key=>$val){
            $orders[$key]['fencheng'] =  $this->fencheng($val['order_id'],$this->user_id);
        }
        $sql = "select count(*) as count from {pre}order_info where parent_id = $user_id and pay_status = '".PS_UNPAYED."'";
        $count_wfk = $this->model->getOne($sql);
        $sql = "select count(*) as count from {pre}order_info where parent_id = $user_id and pay_status = '".PS_PAYED."'";
        $count_yfk = $this->model->getOne($sql);
        $sql = "select count(*) as count from {pre}order_info where parent_id = $user_id and shipping_status = '".SS_RECEIVED."'";
        $count_ywc = $this->model->getOne($sql);
        $this->assign('count_wfk', $count_wfk ? $count_wfk : 0);
        $this->assign('count_yfk', $count_yfk ? $count_yfk : 0);
        $this->assign('count_ywc', $count_ywc ? $count_ywc : 0);
        $this->assign('title', L('order_list'));
        $this->assign('orders_list', $orders);
        $this->display('sale_order_list_wfk.dwt');
    }

    /**
     * 获取全部已付款订单
     */
    public function order_list_yfk() {
        $user_id = I('user_id') > 0 ? I('user_id') : $_SESSION['user_id'];
        $where = 'parent_id = '.$user_id;
        $where=$where." and pay_status = '".PS_PAYED."'";
        $size = I(C('page_size'), 20);
        $sql = "select count(*) as count from {pre}order_info where $where";
        $count = $this->model->getOne($sql);
        $orders = get_sale_orders($where , 10 , 0 ,$user_id);
        foreach ($orders as $key=>$val){
            $orders[$key]['fencheng'] =  $this->fencheng($val['order_id'],$this->user_id);
        }
        $sql = "select count(*) as count from {pre}order_info where parent_id = $user_id and pay_status = '".PS_UNPAYED."'";
        $count_wfk = $this->model->getOne($sql);
        $sql = "select count(*) as count from {pre}order_info where parent_id = $user_id and pay_status = '".PS_PAYED."'";
        $count_yfk = $this->model->getOne($sql);
        $sql = "select count(*) as count from {pre}order_info where parent_id = $user_id and shipping_status = '".SS_RECEIVED."'";
        $count_ywc = $this->model->getOne($sql);
        $this->assign('count_wfk', $count_wfk ? $count_wfk : 0);
        $this->assign('count_yfk', $count_yfk ? $count_yfk : 0);
        $this->assign('count_ywc', $count_ywc ? $count_ywc : 0);
        $this->assign('title', L('order_list'));
        $this->assign('orders_list', $orders);
        $this->display('sale_order_list_yfk.dwt');
    }

    /**
     * 获取全部已完成订单
     */
    public function order_list_ywc() {
        $user_id = I('user_id') > 0 ? I('user_id') : $_SESSION['user_id'];
        $where = 'parent_id = '.$user_id;
        $where=$where." and shipping_status = '".SS_RECEIVED."'";
        $size = I(C('page_size'), 20);
        $sql = "select count(*) as count from {pre}order_info where $where";
        $count = $this->model->getOne($sql);
        $orders = get_sale_orders($where , 10 , 0 ,$user_id);
        foreach ($orders as $key=>$val){
            $orders[$key]['fencheng'] =  $this->fencheng($val['order_id'],$this->user_id);
        }
        $sql = "select count(*) as count from {pre}order_info where parent_id = $user_id and pay_status = '".PS_UNPAYED."'";
        $count_wfk = $this->model->getOne($sql);
        $sql = "select count(*) as count from {pre}order_info where parent_id = $user_id and pay_status = '".PS_PAYED."'";
        $count_yfk = $this->model->getOne($sql);
        $sql = "select count(*) as count from {pre}order_info where parent_id = $user_id and shipping_status = '".SS_RECEIVED."'";
        $count_ywc = $this->model->getOne($sql);
        $this->assign('count_wfk', $count_wfk ? $count_wfk : 0);
        $this->assign('count_yfk', $count_yfk ? $count_yfk : 0);
        $this->assign('count_ywc', $count_ywc ? $count_ywc : 0);
        $this->assign('title', L('order_list'));
        $this->assign('orders_list', $orders);
        $this->display('sale_order_list_ywc.dwt');
    }

    public function my_shop_info(){

        // 总销售额
        $money = model('Sale')->get_shop_sale_money($this->user_id,1);
        $this->assign('money', $money ? $money : '0.00');
        // 一级分店数
        $sql = "select count(*) count from {pre}users as u JOIN {pre}drp_shop d ON  u.user_id=d.user_id WHERE u.parent_id = ".$_SESSION['user_id'];
        $shop_count = $this->model->getRow($sql);
        $this->assign('shop_count', $shop_count['count'] ? $shop_count['count'] : 0);

        // 我的会员数
        $user_count = M()->table('users')->where("parent_id=".$_SESSION['user_id'])->count();
        $this->assign('user_count', $user_count ? $user_count : 0);

        // 店铺订单数
        $order_count = M()->table('order_info')->where("parent_id=".$_SESSION['user_id'])->count();;
        $this->assign('order_count', $order_count ? $order_count : 0);

        $this->assign('title', L('my_shop_info'));
        $this->display('sale_my_shop_info.dwt');
    }

    /**
     * 我的分店
     */
    public function my_shop_list(){
        $key = I('key') ? I('key') : '1';
        $list = model('Sale')->get_shop_list($key);
        $this->assign('list', $list);
        $this->assign('title', L('my_shop_list'.$key));
        $this->display('sale_my_shop_list.dwt');
    }



    /**
     * 微店设置
     */
    public function sale_set(){
        if($this->model->table('drp_shop')->where(array("user_id"=>$_SESSION['user_id'],"open"=>1))->count() > 0){
            redirect(url('sale/index'));
        }
        if (IS_POST){
            $data = I('data');
            if (empty($data['shop_name'])){
                show_message(L('shop_name_empty'));
            }
            if (empty($data['real_name'])){
                show_message(L('real_name_empty'));
            }
            if (empty($data['shop_mobile'])){
                show_message(L('shop_mobile_empty'));
            }
            $data['shop_name'] = C('SHOP_NAME').$data['shop_name'];
            $data['user_id'] = $_SESSION['user_id'];
            if($this->model->table('drp_shop')->data($data)->insert()){
                redirect(url('sale/sale_set_category'));
            }else{
                show_message(L('set_error'));
            }
        }
        $this->assign('title',L('sale_set'));
        $this->display('sale_set.dwt');
    }

    /**
     * 设置分销商品的分类
     */
    public function sale_set_category(){
        if($this->model->table('drp_shop')->where(array("user_id"=>$_SESSION['user_id'],"open"=>1,'cat_id'=>''))->count() > 0){
            redirect(url('sale/index'));
        }
        if(IS_POST){
            $cateArr = I('cate');
            $cat_id = '';
            if($cateArr){
                foreach($cateArr as $key=>$val){
                    $cat_id.=$val.',';
                }
            }else{
                show_message(L('sale_cate_not_empty'));
            }
            $data['cat_id'] = $cat_id;
            $where['user_id'] = $_SESSION['user_id'];
            $this->model->table('drp_shop')->data($data)->where($where)->update();
            redirect(url('sale/sale_set_end'));
        }
        $apply = $this->model->table('drp_config')->field("centent")->where(array("keyword"=>'apply'))->getOne();
        $this->assign('apply',$apply);
        $category = $this->model->table('category')->field("cat_id,cat_name")->where(array("parent_id"=>0))->select();
        if($category){
            foreach($category as $key=>$val){
                $category[$key]['profit1'] = $this->model->table('drp_profit')->field("profit1")->where(array("cate_id"=>$val['cat_id']))->getOne();
                $category[$key]['profit1'] = $category[$key]['profit1'] ? $category[$key]['profit1'] : 0;
                $category[$key]['profit2'] = $this->model->table('drp_profit')->field("profit2")->where(array("cate_id"=>$val['cat_id']))->getOne();
                $category[$key]['profit2'] = $category[$key]['profit2'] ? $category[$key]['profit2'] : 0;
                $category[$key]['profit3'] = $this->model->table('drp_profit')->field("profit3")->where(array("cate_id"=>$val['cat_id']))->getOne();
                $category[$key]['profit3'] = $category[$key]['profit3'] ? $category[$key]['profit3'] : 0;
            }
        }

        $this->assign('category',$category);
        $this->assign('title',L('sale_set_category'));
        $this->display('sale_set_category.dwt');
    }

    /*
     *  设置完成
     */
    public function sale_set_end(){
        // 设置为分销商
        $data['open'] = 1;
        $data['create_time'] = gmtime()+8*3600;
        $where['user_id'] = $_SESSION['user_id'];
        $this->model->table('drp_shop')->data($data)->where($where)->update();
        unset($data);
        unset($where);
        $data['apply_sale'] = 1;
        $data['user_rank'] = 255;
        $where['user_id'] = $_SESSION['user_id'];
        $this->model->table('users')->data($data)->where($where)->update();

        $novice = $this->model->table('drp_config')->field("centent")->where(array("keyword"=>'novice'))->getOne();
        $this->assign('novice',$novice);
        // 设置分销商店铺地址
        $this->assign('sale_url','http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?drp_id='.$_SESSION['user_id']);
        $this->assign('title',L('sale_set_category'));
        $this->display('sale_set_end.dwt');
    }

    /**
     * 未登录验证
     */
    private function check_login() {
        // 分销商不能访问的方法
        $deny = array(
            'sale_set',
            'sale_set_category',
            'sale_set_end',
        );
        // 未登录处理
        if (empty($_SESSION['user_id'])) {
            $url = 'http://'.$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            redirect(url('user/login', array(
                'referer' => urlencode($url)
            )));
            exit();
        }elseif($_SESSION['user_rank'] == 255 && in_array($this->action, $deny)){
            redirect(url('sale/index'));
        }
    }

    /**
     * 我的会员
     */
    public function user_list()
    {
        $key = I('key') ? I('key') : 'wfk';
        $info = model('Sale')->get_sale_info($key);
        $this->assign('info',$info);
        $this->assign('title',L('user_list_'.$key));
        $this->display('sale_user_list.dwt');
    }

    /**
     * 我的N级会员
     */
    public function my_user_list(){
        $key = I('key') ? I('key') : '1';
        $list = model('Sale')->get_user_list($key);
        $this->assign('list',$list);
        $this->assign('title',L('my_user_list'.$key));
        $this->display('sale_my_user_list.dwt');
    }

    /**
     * 我要分销
     */
    public function to_sale(){
        //生成分享连接
        $shopurl = __HOST__.url('index/index',array('sale'=>$this->user_id));
        $this->assign('shopurl', $shopurl);
        $this->assign('domain', __HOST__);
        $this->assign('shopdesc', C('shop_desc'));

        // 生成二维码
        $mobile_url = __URL__; // 二维码内容

        $this->assign('mobile_qr', call_user_func(array('WechatController', 'rec_qrcode'), session('user_name'),$_SESSION['user_id']));

        $this->assign('title','购物去哪儿');
        $this->display('sale_to_code.dwt');
    }

    /**
     * 注册会员
     */
    public function line(){
        $affiliate = unserialize(C('affiliate'));
        $itemCount =  4;//count($affiliate['item']);
        //我的会员数量
        $line_count = saleCount();
        $this->assign('line_count', $line_count);
        $this->assign('itemCount', $itemCount);

        //获取用户A
        $listn2 = array();
        $listn3 = array();
        $listn4 = array();
        if ($itemCount >=1){
            $list = saleList();
            $listCount = count($list);
            if (count($list) > 0){
                //获取用B
                if ($itemCount >=2){
                    foreach ($list as $key => $val){
                        $list2['saleList'] = saleList($val['user_id']);
                        $listn2 = array_merge($list2['saleList'],$listn2);
                        $listCount2+= count($list2['saleList']);
                        if (count($list2['saleList']) > 0){
                            //获取用C
                            if ($itemCount >=3){
                                foreach ($list2['saleList'] as $k => $v){
                                    $list3['saleList'] = saleList($v['user_id']);
                                    $listn3 = array_merge($list3['saleList'],$listn3);
                                    $listCount3+= count($list3['saleList']);
                                    if (count($list3['saleList']) > 0){
                                        //获取用户D
                                        if ($itemCount >=4){
                                            foreach ($list3['saleList'] as $k1 => $v1){
                                                $list4['saleList'] = saleList($v1['user_id']);
                                                $listn4 = array_merge($list4['saleList'],$listn4);
                                                $listCount4+= count($list4['saleList']);
                                            }
                                        }

                                    }
                                }
                            }

                        }

                    }
                }
            }
        }
        $listn2['saleList'] = $listn2;
        $listn3['saleList'] = $listn3;
        $listn4['saleList'] = $listn4;
        //模板赋值
        $this->assign('list',    $list);
        $this->assign('list2',    $listn2 ? $listn2 : array());
        $this->assign('list3',    $listn3 ? $listn3 : array());
        $this->assign('list4',    $listn4 ? $listn4 : array());
        $this->assign('list5',    $listn5 ? $listn5 : array());
        // 销售数量
        $this->assign('listCount',    $listCount > 0 ? $listCount : 0);
        $this->assign('listCount2',    $listCount2 > 0 ? $listCount2 : 0);
        $this->assign('listCount3',    $listCount3 > 0 ? $listCount3 : 0);
        $this->assign('listCount4',    $listCount4 > 0 ? $listCount4 : 0);
        $this->assign('listCount5',    $listCount5 > 0 ? $listCount5 : 0);
        $this->assign('title','注册会员');
        $this->display('sale_line.dwt');
    }



    /**
     * 销售订单详情
     */
    public function order_detail() {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

        // 订单详情
        $order = get_order_detail($order_id, $this->user_id);

        if ($order === false) {
            ECTouch::err()->show(L('back_home_lnk'), './');
            exit();
        }

        // 订单商品
        $goods_list = model('Order')->order_goods($order_id);
        foreach ($goods_list as $key => $value) {
            $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
            $goods_list[$key]['goods_price'] = price_format($value['goods_price'], false);
            $goods_list[$key]['subtotal'] = price_format($value['subtotal'], false);
            $goods_list[$key]['tags'] = model('ClipsBase')->get_tags($value['goods_id']);
            $goods_list[$key]['goods_thumb'] = get_image_path($order_id, $value['goods_thumb']);
        }

        // 订单 支付 配送 状态语言项
        $order['order_status'] = L('os.' . $order['order_status']);
        $order['pay_status'] = L('ps.' . $order['pay_status']);
        $order['shipping_status'] = L('ss.' . $order['shipping_status']);


        $this->assign('title', L('order_detail'));
        $this->assign('order', $order);
        $this->assign('goods_list', $goods_list);
        $this->display('sale_order_detail.dwt');
    }



    public function get_order_count($user_id = 0){
        // 订单数量
        $affiliate = unserialize(C('affiliate'));
        $itemCount =  4;//count($affiliate['item']);
        $ck = 0;
        $grade=array();
        //获取用户A
        if ($itemCount >=1){
            $list = saleuser($user_id);
            $count1 = count($list);
            if (count($list) > 0){
                if ($itemCount >=2){
                    foreach ($list as $key => $val){
                        $grade[$ck]['user_id'] = $val['user_id'];
                        $grade[$ck]['grade'] = 'A';

                        $ck++;

                        //获取用户B
                        $list[$key]['saleList'] = saleuser($val['user_id']);
                        $count2+=count($list[$key]['saleList']);
                        if (count($list[$key]['saleList']) > 0){
                            if ($itemCount >=3){
                                foreach ($list[$key]['saleList'] as $k => $v){
                                    $grade[$ck]['user_id'] = $v['user_id'];
                                    $grade[$ck]['grade'] = 'B';
                                    $ck++;

                                    //获取用户C
                                    $list[$key]['saleList'][$k]['saleList'] = saleuser($v['user_id']);
                                    $count3+=count($list[$key]['saleList'][$k]['saleList']);
                                    if (count($list[$key]['saleList'][$k]['saleList']) > 0){
                                        if ($itemCount >=4){
                                            foreach ($list[$key]['saleList'][$k]['saleList'] as $k1 => $v1){
                                                $grade[$ck]['user_id'] = $v1['user_id'];
                                                $grade[$ck]['grade'] = 'C';
                                                $ck++;

                                                //获取用户D
                                                $list[$key]['saleList'][$k]['saleList'][$k1]['saleList'] = saleuser($v1['user_id']);
                                                $count4+=count($list[$key]['saleList'][$k]['saleList'][$k1]['saleList']);
                                                foreach ($list[$key]['saleList'][$k]['saleList'][$k1]['saleList'] as $k2 => $v2){
                                                    $grade[$ck]['user_id'] = $v2['user_id'];
                                                    $grade[$ck]['grade'] = 'D';
                                                    $ck++;
                                                }
                                            }
                                        }

                                    }
                                }
                            }

                        }

                    }
                }
            }
        }
        $count['count1']=$count1;
        $count['count2']= empty($count2) ? '': $count2;
        $count['count3']= empty($count3) ? '': $count3;
        $count['count4']= empty($count3) ? '': $count3;
        $res = array();
        $res['grade'] = $grade;
        $res['count'] = $count;
        return $res;
    }

    function fencheng($oid,$user_id=0){
        $user_id = $user_id > 0 ? $user_id : $_SESSION['user_id'];
        $affiliate = unserialize(C('affiliate'));
        empty($affiliate) && $affiliate = array();

        $separate_by = $affiliate['config']['separate_by'];
        $row = M()->query("SELECT o.order_sn, o.is_separate, (o.goods_amount - o.discount) AS goods_amount, o.user_id FROM {pre}order_info o".
            " LEFT JOIN {pre}users u ON o.user_id = u.user_id  WHERE order_id = '$oid'");

        $row = $row['0'];

        $row1 = M()->query("SELECT parent_id FROM {pre}users  WHERE user_id = '$row[user_id]'");
        if ($row1['0']['parent_id'] == $user_id){
            $level = 0;
        }else {
            $row2 = M()->query("SELECT parent_id FROM {pre}users  WHERE user_id = '$row1[0][parent_id]'");
            if ($row2['0']['parent_id'] == $user_id){
                $level = 1;
            }else {
                $row3 = M()->query("SELECT parent_id FROM {pre}users  WHERE user_id = '$row2[0][parent_id]'");
                if ($row3['0']['parent_id'] == $user_id){
                    $level = 2;
                }else {return 0;exit;
                    $row4 = M()->query("SELECT parent_id FROM {pre}users  WHERE user_id = '$row3[0][parent_id]'");
                    if ($row4['0']['parent_id'] == $user_id){
                        $level = 3;
                    }else{
                        $row5 = M()->query("SELECT parent_id FROM {pre}users  WHERE user_id = '$row4[0][parent_id]'");
                        if ($row5['0']['parent_id'] == $user_id){
                            $level = 4;
                        }else{
                            return 0;exit;
                        }
                    }
                }
            }
        }


        $order_sn = $row['order_sn'];

        $affiliate['config']['level_point_all'] = (float)$affiliate['config']['level_point_all'];
        $affiliate['config']['level_money_all'] = (float)$affiliate['config']['level_money_all'];
        if ($affiliate['config']['level_point_all'])
        {
            $affiliate['config']['level_point_all'] /= 100;
        }
        if ($affiliate['config']['level_money_all'])
        {
            $affiliate['config']['level_money_all'] /= 100;
        }
        $money = round($affiliate['config']['level_money_all'] * $row['goods_amount'],2);
        $money = (float)$affiliate['item'][$level]['level_money']*$money/100;
        return sprintf("%0.2f", $money);
    }

    /**
     * 添加银行卡
     */
    public function add_bank(){
        if(IS_POST){
            $data = I('data');
            if(empty($data['bank_name'])){
                show_message('请输入银行名称，如：建设银行/支付宝等');
            }
            if(empty($data['bank_card'])){
                show_message('请输入帐号');
            }
            $data['user_id'] = $this->user_id;
            $this->model->table('drp_bank')
                ->data($data)
                ->insert();
            redirect(url('sale/account_raply'));
        }
        $this->assign('title', '添加银行卡');
        $this->display('sale_add_bank.dwt');
    }

    /**
     * 删除银行卡
     */
    public function del_bank(){
        $id = I('id') ? I('id') : 0;
        if($id==0){
            show_message('请选择要删除的银行卡号');
        }
        $this->model->table('drp_bank')->where("id=".$id)->delete();
        redirect(url('sale/account_raply'));
    }

}
