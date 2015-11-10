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
    private $drp = null;

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
        $this->drp = $this->model->table('drp_shop')->where(array("user_id"=>$_SESSION['user_id']))->find();
        $without = array('sale_set', 'sale_set_category', 'sale_set_end' , 'store','spread','apply');
        if(!$this->drp && !in_array($this->action, $without)){
            redirect(url('sale/sale_set'));
        }
        $this->assign('user_id',session('user_id'));
    }


    /**
     * 会员中心欢迎页
     */
    public function index() {
        $shop = $this->model->table('drp_shop')->where(array('user_id'=>$_SESSION['user_id']))->field('create_time,shop_name')->find();
        $sale['time'] = local_date('Y-m-d H:i:s',$shop['create_time']);
        $sale['shop_name'] = C('shop_name').$shop['shop_name'];
        $this->assign('sale',$sale);
        // 总销售额
        $sale_money = model('Sale')->get_sale_money_total();
        $this->assign('sale_money_order',$sale_money ? $sale_money : '0.00');
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
     * 我的店铺
     */
    public function shop_config(){
        if(IS_POST){
            $data = $_POST['data'];
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
            if(!empty($_FILES['shop_img']['name'])){
                $result = $this->uploadImage();
                if ($result['error'] > 0) {
                    show_message($result['message']);
                }

                $data['shop_img'] = $result['message']['shop_img']['savename'];
            }
            $where['user_id'] = $_SESSION['user_id'];
            $this->model->table('drp_shop')->data($data)->where($where)->update();
            show_message(L('success'),'分销中心',url('sale/index'));
        }
        $drp_info = $this->model->table('drp_shop')->field('shop_name,real_name,shop_mobile,shop_img')->where('user_id='.session('user_id'))->select();
        $this->assign('drp_info',$drp_info['0']);
        $this->assign('title', L('shop_config'));
        $this->display('sale_shop_config.dwt');
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
        $dwt = $account_detail ? 'sale_account_detail.dwt' : 'sale_show_message.dwt';
        $this->display($dwt);
    }

    /**
     *  会员申请提现
     */
    public function account_raply(){
        $bank = $this->model->table('drp_shop')->where(array('user_id'=>$_SESSION['user_id']))->field('bank')->find();
        $bank_info = array();
        if($bank['bank']){
            $bank_info = $this->model->table('drp_bank')->where("id=".$bank['bank'])->select();
        }
        $this->assign('bank_info',$bank_info['0']);
        // 获取剩余余额
        $surplus_amount = $this->model->table('drp_shop')->where('user_id='.$this->user_id)->field('money')->getOne();
        if (empty($surplus_amount)) {
            $surplus_amount = 0;
        }
        $this->assign('surplus_amount', price_format($surplus_amount, false));
        $txxz =  $this->model->getRow("select value from {pre}drp_config where keyword='txxz'");
        $this->assign('txxz',$txxz['value']);
        $this->assign('title', L('label_user_surplus'));
        $this->display('sale_account_raply.dwt');
    }

    /**
     *  对会员佣金申请的处理
     */
    public function act_account()
    {
        $bank_id = I('bank');
        $bank = model('Sale')->get_bank_info($bank_id);
        if(!$bank){
            show_message('请选择提现的银行卡');
        }
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        if (!is_numeric($amount)){
            show_message(L('amount_gt_zero'));
        }elseif ($amount <= 0)
        {
            show_message(L('amount_gt_zero'));
        }
        $txxz =  $this->model->getRow("select value from {pre}drp_config where keyword='txxz'");
        if($txxz['value'] > $_POST['amount']){
            show_message('提现金额必须大于'.$txxz['value'].'元');
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
        $sur_amount =  $this->model->table('drp_shop')->where('user_id='.$this->user_id)->field('money')->getOne();
        if ($amount > $sur_amount)
        {
            show_message('佣金金额不足', L('back_page_up'), '', 'info');
        }
        /* 插入帐户变动记录 */
        $account_log = array(
            'user_id'       => $this->user_id,
            'user_money'    => '-'.$amount,
            'change_time'   => gmtime(),
            'change_desc'   => L('drp_log_desc'),
            'bank_info'   => "银行名称：".$bank['bank_name']." 帐号：".$bank['bank_card'],
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

    /**
     * 我的佣金
     */
    public function my_commission(){
        $saleMoney =  model('Sale')->saleMoney_surplus();
        $this->assign('saleMoney',$saleMoney);
        // 未分成销售佣金
        $sale_money = model('sale')->get_shop_sale_money($this->user_id);
        $this->assign('sale_no_money',$sale_money['profit']);
        $this->assign('sale_no_money1',$sale_money['profit1']);
        $this->assign('sale_no_money2',$sale_money['profit2']);
        $this->assign('sale_no_money_num',$sale_money['profit_num']);

        // 已分成销售佣金
        $sale_money = model('sale')->get_shop_sale_money($this->user_id,1);
        $this->assign('sale_money',$sale_money['profit']);
        $this->assign('sale_money1',$sale_money['profit1']);
        $this->assign('sale_money2',$sale_money['profit2']);
        $this->assign('sale_money_num',$sale_money['profit_num']);
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
        $id = I('u') ? I('u') : $this->user_id;
        if(!$id){
            redirect(url('index/index'));
        }
        $filename  = './data/attached/drp';
        if(!file_exists($filename)){
            mkdir($filename);
        }
        $bg_img = ROOT_PATH.'/data/attached/drp/tg-bg.png';//背景图
        $ew_img = 'data/attached/drp/tg-ew-'.$id.'.png';//二维码
        $dp_img = 'data/attached/drp/tg-'.$id.'.png';//店铺二维码
        $wx_img = 'data/attached/drp/wx-'.$id.'.png';//微信头像
        if(!file_exists($ew_img)){
            $b = call_user_func(array('WechatController', 'rec_qrcode'), session('user_name'),session('user_id'));
            $b=preg_replace('/https/','http',$b,1);
            $img = @file_get_contents($b);
            file_put_contents($ew_img,$img);
            Image::thumb($ew_img, $ew_img,'','330','330'); // 将图片重新设置大小
        }

        // 获取微信头像
        if(class_exists('WechatController')){
            if (method_exists('WechatController', 'get_avatar')) {
                $info = call_user_func(array('WechatController', 'get_avatar'), $id);
            }
        }
        if($info['avatar']){
            $ch = curl_init();
            $timeout = 5;
            $info['avatar']=preg_replace('/https/','http',$info['avatar'],1);
            curl_setopt ($ch, CURLOPT_URL, $info['avatar']);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en; rv:1.9.2) Gecko/20100115 Firefox/3.6 GTBDFff GTB7.0');
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $thumb = curl_exec($ch);
            curl_close($ch);
            file_put_contents($wx_img,$thumb);

            Image::thumb($wx_img, $wx_img,'','100','100'); // 将图片重新设置大小
        }


        // 生成海报图片
        $img = file_get_contents($bg_img);
        file_put_contents($dp_img,$img);
        chmod(ROOT_PATH.$dp_img, 0777);

        // 添加二维码水印
        if(file_get_contents($ew_img)){
            Image::water($dp_img,$ew_img,12);
        }

        // 添加微信头像水印
        if($info['avatar']){
            Image::water($dp_img,$wx_img,13);
        }

        // 销售二维码
        $this->assign('mobile_qr', $dp_img);

        $this->assign('title',L('spread'));
        $this->display('sale_spread.dwt');
    }

    /**
     * 店铺二维码
     */
    public function store(){
        $id = I('u') ? I('u') : $this->user_id;
        if(!$id){
            redirect(url('index/index'));
        }
        $filename  = './data/attached/drp';
        if(!file_exists($filename)){
            mkdir($filename);
        }

        $bg_img = ROOT_PATH.'/data/attached/drp/dp-bg.png';//背景图
        $ew_img = 'data/attached/drp/drp-'.$id.'.png';//二维码
        $dp_img = 'data/attached/drp/dp-'.$id.'.png';//店铺二维码
        $wx_img = 'data/attached/drp/wx-'.$id.'.png';//微信头像
//        if(!file_exists($ew_img)){
        $drp_id = M()->table('drp_shop')->field('id')->where("user_id=".$id)->getOne();
        // 二维码
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?u='.$id.'&drp_id='.$drp_id;
        // 纠错级别：L、M、Q、H
        $errorCorrectionLevel = 'M';
        // 点的大小：1到10
        $matrixPointSize = 13;
        @QRcode::png($url, $ew_img, $errorCorrectionLevel, $matrixPointSize, 2);
//        }

        // 获取微信头像

        $info = model('ClipsBase')->get_user_default($id);
        if(class_exists('WechatController')){
            if (method_exists('WechatController', 'get_avatar')) {
                $info = call_user_func(array('WechatController', 'get_avatar'), $id);
            }
        }
        if($info['avatar']){
            $ch = curl_init();
            $timeout = 5;
            $info['avatar']=preg_replace('/https/','http',$info['avatar'],1);

            curl_setopt ($ch, CURLOPT_URL, $info['avatar']);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en; rv:1.9.2) Gecko/20100115 Firefox/3.6 GTBDFff GTB7.0');
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $thumb = curl_exec($ch);
            curl_close($ch);
            file_put_contents($wx_img,$thumb);
            Image::thumb($wx_img, $wx_img,'','100','100'); // 将图片重新设置大小
        }

        // 生成海报图片
        $img = file_get_contents($bg_img);
        file_put_contents($dp_img,$img);
        chmod(ROOT_PATH.$dp_img, 0777);
        // 添加二维码水印
        Image::water($dp_img,$ew_img,10);
        // 添加微信头像水印
        if($info['avatar']) {
            Image::water($dp_img, $wx_img, 11);
        }
        $this->assign('mobile_qr', $dp_img);
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
        $where = 'd.drp_id = '.$drp_id;
        $sql = "select count(*) as count from {pre}drp_order_info as d right join {pre}order_info as o on d.order_id=o.order_id where d.drp_id=$drp_id";
        $count = $this->model->getRow($sql);
        $count = $count['count'] ? $count['count'] : 0;
        $this->pageLimit(url('sale/order_list'), $size);
        $this->assign('pager', $this->pageShow($count));
        $orders = model('Sale')->get_sale_orders($where ,  $size, ($page-1)*$size,$user_id);

        if($orders){
            foreach($orders as $key=>$val){
                foreach($val['goods'] as $k=>$v){
                    $orders[$key]['goods'][$k]['profit'] = model('Sale')->get_drp_order_profit($val['order_id'],$v['goods_id']);
                }
            }
        }

        $this->assign('orders_list', $orders);
        $this->assign('title', L('order_list'));
        $dwt = $orders ? 'sale_order_list.dwt' : 'sale_show_message.dwt';
        $this->display($dwt);
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
        $this->assign('money', $money['profit'] ? $money['profit'] : '0.00');
        // 一级分店数
        $sql = "select count(*) count from {pre}users as u JOIN {pre}drp_shop d ON  u.user_id=d.user_id WHERE u.parent_id = ".$_SESSION['user_id'];
        $shop_count = $this->model->getRow($sql);
        $this->assign('shop_count', $shop_count['count'] ? $shop_count['count'] : 0);

        // 我的会员数
        $user_count = M()->table('users')->where("parent_id=".$_SESSION['user_id'])->count();
        $this->assign('user_count', $user_count ? $user_count : 0);

        // 店铺订单数
        $sql = "select count(*) as count from {pre}drp_order_info where drp_id = ".$this->drp['id'];
        $order_count = $this->model->getRow($sql);
        $this->assign('order_count', $order_count['count'] ? $order_count['count'] : 0);

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
        $dwt = $list ? 'sale_my_shop_list.dwt' : 'sale_show_message.dwt';
        $this->display($dwt);
    }



    /**
     * 微店设置
     */
    public function sale_set(){
        $info = $this->model->table('drp_shop')->where(array("user_id"=>$_SESSION['user_id']))->select();
        if($info){
            if($info['0']['cat_id']==''){
                redirect(url('sale/sale_set_category'));
            }
            else{
                redirect(url('sale/index'));
            }

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
            $data['shop_name'] = $data['shop_name'];
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
            $data['open'] = 1;
            $where['user_id'] = $_SESSION['user_id'];
            $this->model->table('drp_shop')->data($data)->where($where)->update();
            redirect(url('sale/sale_set_end'));
        }
        $apply = $this->model->table('drp_config')->field("value")->where(array("keyword"=>'apply'))->getOne();
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
        $data['create_time'] = gmtime();
        $where['user_id'] = $_SESSION['user_id'];
        $this->model->table('drp_shop')->data($data)->where($where)->update();
        unset($data);
        unset($where);
        $data['user_rank'] = 255;
        $where['user_id'] = $_SESSION['user_id'];
        $this->model->table('users')->data($data)->where($where)->update();
        $novice = $this->model->table('drp_config')->field("value")->where(array("keyword"=>'novice'))->getOne();
        $this->assign('novice',$novice);
        // 设置分销商店铺地址
        $drp_id = M()->table('drp_shop')->field('id')->where("user_id=".$_SESSION['user_id'])->getOne();
        $this->assign('sale_url','http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?u='.$_SESSION['user_id'].'&drp_id='.$drp_id);
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
        $shareArr = array(
            'store',
            'spread',
        );
        // 未登录处理
        if (empty($_SESSION['user_id']) && !in_array($this->action, $shareArr)) {
            $url = 'http://'.$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            redirect(url('user/login', array(
                'referer' => urlencode($url)
            )));
            exit();
        }elseif($_SESSION['user_rank'] == 255 && in_array($this->action, $deny) && !in_array($this->action, $shareArr)){
            redirect(url('sale/index'));
        }

        // 增加判断
        $examine = $this->model->table('drp_config')->field('value')->where('keyword = "examine"')->getOne();
        if($examine == 'open' && $this->action !='apply'){
            $is_apply = $this->model->table('drp_apply')->field('apply')->where('user_id ='.session('user_id'))->getOne();
            if($is_apply != 2 ){
                redirect(url('sale/apply'));
            }
        }
        if(model('Sale')->get_drp_status() == 0){
            show_message(L('drp_close'),L('home'),url());
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
        $dwt = $info ? 'sale_user_list.dwt' : 'sale_show_message.dwt';
        $this->display($dwt);
    }

    /**
     * 我的N级会员
     */
    public function my_user_list(){
        $key = I('key') ? I('key') : '1';
        $list = model('Sale')->get_user_list($key);
        $this->assign('list',$list);
        $this->assign('title',L('my_user_list'.$key));

        $dwt = $list ? 'sale_my_user_list.dwt' : 'sale_show_message.dwt';
        $this->display($dwt);
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
    public function select_bank(){
        if(IS_POST){
            $bank = I('bank') ? I('bank') : 0;
            if($bank==0){
                show_message('请选择银行卡');
            }
            $data['bank'] = $bank;
            $this->model->table('drp_shop')->data($data)->where("user_id=".$this->user_id)->update();
            redirect(url('sale/account_raply'));

        }
        $list = $this->model->table('drp_bank')->where("user_id=".$this->user_id)->select();
        $this->assign('list',$list);
        $this->assign('title','选择默认银行卡');
        $this->display('sale_select_bank.dwt');
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

    /**
     * 店铺详情
     * @throws Exception
     */
    public function shop_detail(){
        $id = I('id') ? I('id') : $this->user_id;
        $info = M()->table('drp_shop')->where("user_id=".$id)->select();
        $info['0']['time'] = local_date('Y-m-d H:i:s',$info['0']['create_time']);
        $info['0']['shop_name'] = C('shop_name').$info['0']['shop_name'];
        $this->assign('shop_info', $info['0']);

        $shop_user = model('ClipsBase')->get_user_default($id);
        $this->assign('shop_user', $shop_user);
        // 总销售额
        $money = model('Sale')->get_sale_money_total($id);
        $this->assign('money', $money ? $money : '0.00');
        // 一级分店数
        $sql = "select count(*) count from {pre}users as u JOIN {pre}drp_shop d ON  u.user_id=d.user_id WHERE u.parent_id = ".$id;
        $shop_count = $this->model->getRow($sql);
        $this->assign('shop_count', $shop_count['count'] ? $shop_count['count'] : 0);

        // 我的会员数
        $user_count = M()->table('users')->where("parent_id=".$id)->count();
        $this->assign('user_count', $user_count ? $user_count : 0);

        // 店铺订单数
        $order_count = M()->table('order_info')->where("drp_id=".$info['0']['id'])->count();;
        $this->assign('order_count', $order_count ? $order_count : 0);
        $this->assign('title', '店铺详情');
        $this->display('sale_shop_detail.dwt');
    }

    /**
     * 分销商排行榜
     */
    public function ranking_list(){

        $size = I(C('page_size'), 10);
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $sql = "select COUNT(*) as count from {pre}drp_shop";
        $count = $this->model->query($sql);
        $count = $count['0']['count'];
        $this->pageLimit(url('sale/ranking_list'), $size);
        $this->assign('pager', $this->pageShow($count));

        $sql = "select *, (select sum(user_money) from {pre}drp_log WHERE user_money > 0 and status=1 and  {pre}drp_log.user_id= {pre}drp_shop.user_id) as sum_money from {pre}drp_shop order by sum_money desc limit  ".($page-1)*$size.','.$size;
        $list = $this->model->query($sql);
        if($list){
            foreach($list as $key=>$val){
                $list[$key]['sum_money'] = $val['sum_money'] ? $val['sum_money'] : 0.00;
                $list[$key]['shop_name'] = C('shop_name').$val['shop_name'];
            }
        }
        $this->assign('list', $list);
        $this->assign('title', L('ranking_list'));
        $this->display('sale_ranking_list.dwt');
    }



    /**
     * 销售订单详情
     */
    public function order_detail() {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

        if ($order_id == 0) {
            ECTouch::err()->show(L('back_home_lnk'), './');
            exit();
        }
        $where = 'd.order_id = '.$order_id;
        $orders = model('Sale')->get_sale_orders($where,1,0,0);
        if($orders){
            foreach($orders as $key=>$val){
                foreach($val['goods'] as $k=>$v){
                    $orders[$key]['goods'][$k]['profit'] = model('Sale')->get_drp_profit($v['goods_id']);
                    $orders[$key]['goods'][$k]['profit_money'] = $v['touch_sale']*$orders[$key]['goods'][$k]['profit']['profit1'] /100;
                    $orders[$key]['sum']+=$orders[$key]['goods'][$k]['profit_money']*$v['goods_number'];
                }
            }
        }
        $this->assign('orders_list', $orders);
        $this->assign('title', L('order_detail'));
        $this->display('sale_order_detail.dwt');
    }

    /**
     * 上传图片
     * @return multitype:number type
     */
    public function uploadImage(){
        $upload = new UploadFile();
        //设置上传文件类型
        $upload->allowExts = explode(',', 'jpg,jpeg,gif,png,bmp');
        //设置附件上传目录
        $upload->savePath = './data/attached/drp_logo/';
        // 是否生成缩略图
        $upload->thumb = false;
        //缩略图大小
        $upload->thumbMaxWidth = 500;
        $upload->thumbMaxHeight = 500;
        if (!$upload->upload($key)) {
            //捕获上传异常
            return array('error' => 1, 'message' => $upload->getErrorMsg());
        } else {
            //取得成功上传的文件信息
            return array('error' => 0, 'message' => $upload->getUploadFileInfo());
        }
    }

    /**
     * 分销商审核中
     */
    public function examine(){
        $this->assign('title','分销商审核');
        $this->display('sale_examine.dwt');
    }

    public function apply(){

        $apply_info = $this->model->table('drp_apply')->where("user_id=".session('user_id'))->find();
        $price = $this->model->table('drp_config')->where("keyword='money'")->field('value')->getOne();

        if($apply_info['apply'] == 2){
            redirect(url('sale/index'));exit;
        }
        if($apply_info){
            if($apply_info['amount'] != $price){
                $data['amount'] = $price;
                $where['user_id'] = session('user_id');
                $this->model->table('drp_apply')->data($data)->where($where)->update();
            }
            $apply_id = $apply_info['id'];
        }else{
            unset($apply_info);
            // 生成支付记录
            $apply_info['apply'] = 1;
            $apply_info['user_id'] = session('user_id');
            $apply_info['time'] = gmtime();
            $apply_info['amount'] = $price;

            $this->model->table('drp_apply')
                ->data($apply_info)
                ->insert();

            $apply_id = $this->model->insert_id();
        }
        /* 取得支付信息，生成支付代码 */
        if ($apply_info ['amount'] > 0) {

            $sql = "SELECT * FROM {pre}payment WHERE pay_code = 'wxpay' AND enabled = 1";
            $payment = $this->model->getRow($sql);

            include_once (ROOT_PATH . 'plugins/payment/' . $payment ['pay_code'] . '.php');

            $pay_obj = new $payment ['pay_code'] ();
            //补全支付信息
            $apply_info['order_amount'] = $apply_info ['amount'];
            $apply_info['order_sn'] = get_order_sn();
            $apply_info['log_id'] = $apply_id;

            $pay_online = $pay_obj->get_code($apply_info, unserialize_config($payment ['pay_config']));

            $this->assign('pay_online',$pay_online);

        }

        $headimgurl = $this->model->table('wechat_user')->field('headimgurl')->where('ect_uid = '.session('user_id'))->getOne();
        $this->assign('headimgurl',$headimgurl);

        $money = $this->model->table('drp_config')->field('value')->where('keyword = "money"')->getOne();
        $this->assign('money',$money);
        $this->assign('title','分销申请');
        $this->display('sale_apply.dwt');


    }
}
