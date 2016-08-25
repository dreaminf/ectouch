<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：IndexController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTouch众筹分类商品购物流程控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class CrowdflowController extends CommonController {
	 /**
     * 构造函数
     */
    public function __construct()
    {
		parent::__construct();
		
    }
	
	/**
     * 众筹项目订单确认页
     */
    public function crowd_checkout() {
		/* 取得购物类型 */
        //$flow_type = isset($_SESSION ['flow_type']) ? intval($_SESSION ['flow_type']) : CART_GENERAL_GOODS;
		if(!empty($_POST)){
			$goods_id = I('request.goods_id');
			$cp_id = I('request.cp_id');
			$number = I('request.number');

			$_SESSION['goods_id'] =$goods_id ;
			$_SESSION['cp_id'] = $cp_id ;
			$_SESSION['number'] = $number ;			
		}else{
			$_SESSION['goods_id'];
			$_SESSION['cp_id'];
			$_SESSION['number'];			
		}
		$this->assign('goods_id', $_SESSION['goods_id']);
		$this->assign('cp_id', $_SESSION['cp_id']);
		$this->assign('number', $_SESSION['number']);
		
		//  检查用户是否已经登录 如果用户已经登录了则检查是否有默认的收货地址 如果没有登录则跳转到登录和注册页面
        if (empty($_SESSION ['direct_shopping']) && $_SESSION ['user_id'] == 0) {
            /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
            $this->redirect(url('user/login',array('step'=>'crowdflow')));
            exit;
        }
        // 获取收货人信息
        $consignee = model('Order')->get_consignee($_SESSION ['user_id']);
        /* 检查收货人信息是否完整 */
        if (!model('Order')->check_consignee_info($consignee, $flow_type)) {
            /* 如果不完整则转向到收货人信息填写界面 */
            ecs_header("Location: " . url('crowdflow/crowd_consignee_list') . "\n");
        }
		
		/* 对商品信息赋值 */
		$cart_goods = model('Crowdbuy')->cart_crowd_goods($_SESSION['goods_id'], $_SESSION['cp_id'], $_SESSION['number']);  //项目信息
		$this->assign('goods', $cart_goods);
		
		// 取得订单信息
        $order = model('Crowdbuy')->crowd_flow_order_info();
		$this->assign('order', $order);
		
        // 获取配送地址
        $consignee_list = model('Users')->get_consignee_list($_SESSION ['user_id']);
        $this->assign('consignee_list', $consignee_list);
        //获取默认配送地址
        $address_id = $this->model->table('users')->field('address_id')->where("user_id = '" . $_SESSION['user_id'] . "' ")->getOne();
        $this->assign('address_id', $address_id);

        $_SESSION ['flow_consignee'] = $consignee;
        $this->assign('consignee', $consignee);		
		//计算订单的费用
        $total = model('Crowdbuy')->crowd_order_fee($order, $cart_goods, $consignee);
		$this->assign('total', $total);
	
		
		/* 取得配送列表 */
		$region = array(
            $consignee ['country'],
            $consignee ['province'],
            $consignee ['city'],
            $consignee ['district']
        );
        $shipping_list = model('Shipping')->available_shipping_list($region);
		$cart_weight_price = model('Order')->cart_weight_price($flow_type);
        $insure_disabled = true;
        $cod_disabled = true;

        foreach ($shipping_list as $key => $val) {

            $shipping_cfg = unserialize_config($val ['configure']);
            $shipping_fee = shipping_fee($val['shipping_code'], unserialize($val ['configure']), $cart_weight_price ['weight'], $cart_weight_price ['amount'], $cart_weight_price ['number']);

            $shipping_list [$key] ['format_shipping_fee'] = price_format($shipping_fee, false);
            $shipping_list [$key] ['shipping_fee'] = $shipping_fee;
            $shipping_list [$key] ['free_money'] = price_format($shipping_cfg ['free_money'], false);
            $shipping_list [$key] ['insure_formated'] = strpos($val ['insure'], '%') === false ? price_format($val ['insure'], false) : $val ['insure'];

            /* 当前的配送方式是否支持保价 */
            if ($val ['shipping_id'] == $order ['shipping_id']) {
                $insure_disabled = ($val ['insure'] == 0);
                $cod_disabled = ($val ['support_cod'] == 0);
            }
	        // 兼容过滤ecjia配送方式
            if (substr($val['shipping_code'], 0 , 5) == 'ship_') {
                unset($shipping_list[$key]);
            }
        }
        $this->assign('shipping_list', $shipping_list);
        $this->assign('insure_disabled', $insure_disabled);
        $this->assign('cod_disabled', $cod_disabled);
		
		
		/* 取得支付列表 */
        if ($order ['shipping_id'] == 0) {
            $cod = true;
            $cod_fee = 0;
        } else {
            $shipping = model('Shipping')->shipping_info($order ['shipping_id']);
            $cod = $shipping ['support_cod'];

            if ($cod) {
                /* 如果是团购，且保证金大于0，不能使用货到付款 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $group_buy_id = $_SESSION ['extension_id'];
                    if ($group_buy_id <= 0) {
                        show_message('error group_buy_id');
                    }
                    $group_buy = model('GroupBuyBase')->group_buy_info($group_buy_id);
                    if (empty($group_buy)) {
                        show_message('group buy not exists: ' . $group_buy_id);
                    }

                    if ($group_buy ['deposit'] > 0) {
                        $cod = false;
                        $cod_fee = 0;

                        /* 赋值保证金 */
                        $this->assign('gb_deposit', $group_buy ['deposit']);
                    }
                }

                if ($cod) {
                    $shipping_area_info = model('Shipping')->shipping_area_info($order ['shipping_id'], $region);
                    $cod_fee = $shipping_area_info ['pay_fee'];
                }
            } else {
                $cod_fee = 0;
            }
        }

        // 给货到付款的手续费加<span id>，以便改变配送的时候动态显示
        $payment_list = model('Order')->available_payment_list(1, $cod_fee);
        if (isset($payment_list)) {
            foreach ($payment_list as $key => $payment) {
                // 只保留显示手机版支付方式
                if(!file_exists(ROOT_PATH . 'plugins/payment/'.$payment['pay_code'].'.php')){
                    unset($payment_list[$key]);
                }
                if ($payment ['is_cod'] == '1') {
                    $payment_list [$key] ['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment ['format_pay_fee'] . '</span>';
                }

                /* 如果有易宝神州行支付 如果订单金额大于300 则不显示 */
                if ($payment ['pay_code'] == 'yeepayszx' && $total ['amount'] > 300) {
                    unset($payment_list [$key]);
                }
                /* 如果有余额支付 */
                if ($payment ['pay_code'] == 'balance') {
                    /* 如果未登录，不显示 */
                    if ($_SESSION ['user_id'] == 0) {
                        unset($payment_list [$key]);
                    } else {
                        if ($_SESSION ['flow_order'] ['pay_id'] == $payment ['pay_id']) {
                            $this->assign('disable_surplus', 1);
                        }
                    }
                }
                // 如果不是微信浏览器访问并且不是微信会员 则不显示微信支付
                if ($payment ['pay_code'] == 'wxpay' && !is_wechat_browser() && empty($_SESSION['openid'])) {
                    unset($payment_list [$key]);
                }
                // 兼容过滤ecjia支付方式
                if (substr($payment['pay_code'], 0 , 4) == 'pay_') {
                    unset($payment_list[$key]);
                }
            }
        }
        $this->assign('payment_list', $payment_list);
		
		$this->assign('number', $_SESSION['number']);
		
	    $this->display('crowd/raise_checkout.html');
	   
    }
	
	
	
	/**
     * 收货地址列表
     */
    public function crowd_consignee_list() {


            // 获得用户所有的收货人信息
            $consignee_list = model('Users')->get_consignee_list($_SESSION['user_id'], 0);

            if ($consignee_list) {
                foreach ($consignee_list as $k => $v) {
                    $address = '';
                    if ($v['province']) {
                        $address .= model('RegionBase')->get_region_name($v['province']);
                    }
                    if ($v['city']) {
                        $address .= model('RegionBase')->get_region_name($v['city']);
                    }
                    if ($v['district']) {
                        $address .= model('RegionBase')->get_region_name($v['district']);
                    }
                    $consignee_list[$k]['address'] = $address . ' ' . $v['address'];
                    $consignee_list[$k]['url'] = url('crowdflow/crowd_consignee', array('id' => $v ['address_id']));
                }
            }
        // 赋值于模板
		$this->assign('consignee_list', $consignee_list);
        $this->assign('title', L('consignee_info'));
	
		$this->display('crowd/raise_flow_consignee_list.html');
	}
	
	
	/**
     * 收货地址添加修改
     */
    public function crowd_consignee() {
		  if ($_SERVER ['REQUEST_METHOD'] == 'GET') {
            /* 取得购物类型 */
            $flow_type = isset($_SESSION ['flow_type']) ? intval($_SESSION ['flow_type']) : CART_GENERAL_GOODS;
            //收货人信息填写界面
            if (isset($_REQUEST ['direct_shopping'])) {
                $_SESSION ['direct_shopping'] = 1;
            }

            /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
            $this->assign('country_list', model('RegionBase')->get_regions());
            $this->assign('shop_country', C('shop_country'));
            $this->assign('shop_province_list', model('RegionBase')->get_regions(1, C('shop_country')));

            /* 获得用户所有的收货人信息 */
            if ($_SESSION ['user_id'] > 0) {
                $addressId = I('get.id');
                if ($addressId > 0) {
                    $consignee_list[] = model('Users')->get_consignee_list($_SESSION ['user_id'], $addressId);
                } else {
					if(!empty($_SESSION['consignee'])){
						$consignee = $_SESSION['consignee'];
						$consignee_list [] = array(
                        'country' => C('shop_country'),
						'province' => $consignee['province'],						   
						'city' => $consignee['city'],						   
						'district' => $consignee['district'],
						   
						);
					}else{
						$consignee_list [] = array(
                        'country' => C('shop_country'),
						);						
					}                    
                }
            } else {
                if (isset($_SESSION ['flow_consignee'])) {
                    $consignee_list = array(
                        $_SESSION ['flow_consignee']
                    );
                } else {
                    $consignee_list [] = array(
                        'country' => C('shop_country')
                    );
                }
            }
            $this->assign('name_of_region', array(
                C('name_of_region_1'),
                C('name_of_region_2'),
                C('name_of_region_3'),
                C('name_of_region_4')
            ));
            $this->assign('consignee_list', $consignee_list);

            /* 取得每个收货地址的省市区列表 */
            $city_list = array();
            $district_list = array();
            foreach ($consignee_list as $region_id => $consignee) {
                $consignee ['country'] = isset($consignee ['country']) ? intval($consignee ['country']) : 1;
                $consignee ['province'] = isset($consignee ['province']) ? intval($consignee ['province']) : 0;
                $consignee ['city'] = isset($consignee ['city']) ? intval($consignee ['city']) : 0;

                $city_list [$region_id] = model('RegionBase')->get_regions(2, $consignee ['province']);
                $district_list [$region_id] = model('RegionBase')->get_regions(3, $consignee ['city']);
            }
            $this->assign('province_list', model('RegionBase')->get_regions(1, $consignee ['country']));
            $this->assign('city_list', $city_list);
            $this->assign('district_list', $district_list);

            /* 返回收货人页面代码 */
            $this->assign('real_goods_count', model('Order')->exist_real_goods(0, $flow_type) ? 1 : 0 );
        } else {
            /*  保存收货人信息 	 */
            $consignee = array(
                'address_id' => empty($_POST ['address_id']) ? 0 : intval($_POST ['address_id']),
                'consignee' => empty($_POST ['consignee']) ? '' : I('post.consignee'),
                'country' => empty($_POST ['country']) ? '' : intval($_POST ['country']),
                'province' => empty($_POST ['province']) ? '' : intval($_POST ['province']),
                'city' => empty($_POST ['city']) ? '' : intval($_POST ['city']),
                'district' => empty($_POST ['district']) ? '' : intval($_POST ['district']),
                'address' => empty($_POST ['address']) ? '' : I('post.address'),
                'mobile' => empty($_POST ['mobile']) ? '' : make_semiangle(I('post.mobile'))
            );

            if ($_SESSION ['user_id'] > 0) {
                /* 如果用户已经登录，则保存收货人信息 */
                $consignee ['user_id'] = $_SESSION ['user_id'];
                model('Users')->save_consignee($consignee, true);
            }

            /* 保存到session */
            $_SESSION ['flow_consignee'] = stripslashes_deep($consignee);
            ecs_header("Location: " . url('crowdflow/crowd_checkout') . "\n");
        }

        $this->assign('currency_format', C('currency_format'));
        $this->assign('integral_scale', C('integral_scale'));
        $this->assign('step', ACTION_NAME);
        $this->assign('title', L('consignee_info'));

		$this->display('crowd/raise_flow_consignee.html');
	}
	
	/*设置默认收货地址*/
	public function crowd_edit_address_info() {
		if (IS_AJAX && IS_AJAX) {
            $address_id = I('id');
			//print_r($address_id);
			$data['address_id'] = $address_id;
            $condition['user_id'] = $_SESSION['user_id'];
			$this->model->table('users')->data($data)->where($condition)->update();	
			unset($_SESSION['flow_consignee']);
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
         }
		 
	}
	
	 /**
     * 删除收货地址
     */
    public function crowd_del_address_list() {
        $id = intval($_GET['id']);

        if (model('Users')->drop_consignee($id)) {
            $url = url('crowd_consignee_list');
            ecs_header("Location: $url\n");
            exit();
        } else {
            show_message(L('del_address_false'));
        }
    }
	
	/**
     *  提交订单
     */
	 public function crowd_done() {
		
		$goods_id = I('post.goods_id', 0);
		$cp_id = I('post.cp_id', 0);
		$number = I('post.number', 0);
		$_SESSION['goods_id'] =$goods_id ;
		$_SESSION['cp_id'] = $cp_id ;
		$_SESSION['number'] = $number ;		
		if(empty($_SESSION['goods_id']) && empty($_SESSION['cp_id'])&&empty($_SESSION['number'])){
			ecs_header("Location: " . url('index/index') . "\n");
			 
		 }		
        // 检查用户是否已经登录 如果用户已经登录了则检查是否有默认的收货地址 如果没有登录则跳转到登录和注册页面
        if (empty($_SESSION ['direct_shopping']) && $_SESSION ['user_id'] == 0) {
            /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
            ecs_header("Location: " . url('user/login') . "\n");
        }
		
		/*判断重复商品订单 是否支付 */		
		$condition = "user_id = '".$_SESSION[user_id]."' AND goods_id = '".$_SESSION['goods_id']."' AND cp_id = '".$_SESSION['cp_id']."' AND pay_status = 0 ";
        $order_num = $this->model->table('crowd_order_info')->field('count(order_id)')->where($condition)->getOne();
		if($order_num > 0)
		{
			//show_message('您有未支付的众筹订单，请付款后再提交新订单','返回上一页',U('mycrowd/index/order'));
			crowd_show_message('您有未支付的众筹订单，请付款后再提交新订单', '去支付', url('mycrowd/crowd_order'), 'info');
		}

		
        // 获取收货人信息
        $consignee = model('Order')->get_consignee($_SESSION ['user_id']);
        /* 检查收货人信息是否完整 */
        if (!model('Order')->check_consignee_info($consignee, $flow_type)) {
            /* 如果不完整则转向到收货人信息填写界面 */
            ecs_header("Location: " . url('crowdflow/crowd_consignee_list') . "\n");
        }

        // 处理接收信息
        $how_oos = I('post.how_oos', 0);
        $card_message = I('post.card_message',  '');
        $inv_type = I('post.inv_type', '');
        $inv_payee = I('post.inv_payee', '');
        $inv_content = I('post.inv_content','');
        $postscript = I('post.postscript', '');
        $oos = L('oos.' . $how_oos);
        // 订单信息
        $order = array(
            'shipping_id' => I('post.shipping_id'),
            'pay_id' => I('post.payment_id'), // 付款方式
            'pack_id' => I('post.pack', 0),
            'card_id' => isset($_POST ['card']) ? intval($_POST ['card']) : 0,
            'card_message' => $card_message,
            'surplus' => isset($_POST ['surplus']) ? floatval($_POST ['surplus']) : 0.00,
            'integral' => isset($_POST ['integral']) ? intval($_POST ['integral']) : 0,
            'bonus_id' => isset($_POST ['bonus']) ? intval($_POST ['bonus']) : 0,
            'need_inv' => empty($_POST ['need_inv']) ? 0 : 1,
            'inv_type' => $inv_type,
            'inv_payee' => $inv_payee,
            'inv_content' => $inv_content,
            'postscript' => $postscript,//订单留言
            'how_oos' => isset($oos) ? addslashes("$oos") : '',
            'need_insure' => isset($_POST ['need_insure']) ? intval($_POST ['need_insure']) : 0,
            'user_id' => $_SESSION ['user_id'],
            'add_time' => time(),
            'order_status' => OS_UNCONFIRMED,
            'shipping_status' => SS_UNSHIPPED,
            'pay_status' => PS_UNPAYED,
            'agency_id' => model('Order')->get_agency_by_regions(array(
                $consignee ['country'],
                $consignee ['province'],
                $consignee ['city'],
                $consignee ['district']
            ))
        );

		
		/* 检查积分余额是否合法 */
        $user_id = $_SESSION ['user_id'];
        if ($user_id > 0) {

            $user_info = model('Order')->user_info($user_id);
            $order ['surplus'] = min($order ['surplus'], $user_info ['user_money'] + $user_info ['credit_line']);
            if ($order ['surplus'] < 0) {
                $order ['surplus'] = 0;
            }

            // 查询用户有多少积分
            $flow_points = model('Flow')->flow_available_points(); // 该订单允许使用的积分
            $user_points = $user_info ['pay_points']; // 用户的积分总数

            $order ['integral'] = min($order ['integral'], $user_points, $flow_points);
            if ($order ['integral'] < 0) {
                $order ['integral'] = 0;
            }
        } else {
            $order ['surplus'] = 0;
            $order ['integral'] = 0;
        }

		
		
        /* 订单中的商品 */
        $cart_goods = model('Crowdbuy')->cart_crowd_goods($_SESSION['goods_id'], $_SESSION['cp_id'], $_SESSION['number']);  
        if (empty($cart_goods)) {
            show_message(L('no_goods_in_cart'), L('back_home'), './', 'warning');
        }
        /* 检查商品总额是否达到最低限购金额 */
        if ($flow_type == CART_GENERAL_GOODS && model('Order')->cart_amount(true, CART_GENERAL_GOODS) < C('min_goods_amount')) {
            show_message(sprintf(L('goods_amount_not_enough'), price_format(C('min_goods_amount'), false)));
        }
		
        /* 收货人信息 */
        foreach ($consignee as $key => $value) {
            $order [$key] = addslashes($value);
        }

        /* 订单中的总额 */
        $total = model('Crowdbuy')->crowd_order_fee($order, $cart_goods, $consignee);
        $order ['bonus'] = $total ['bonus'];
        $order ['goods_amount'] = $total ['goods_price'];
        $order ['discount'] = $total ['discount'];
        $order ['surplus'] = $total ['surplus'];
        $order ['tax'] = $total ['tax'];

        // 购物车中的商品能享受红包支付的总额
        $discount_amout = model('Order')->compute_discount_amount();
        // 红包和积分最多能支付的金额为商品总额
        $temp_amout = $order ['goods_amount'] - $discount_amout;
        if ($temp_amout <= 0) {
            $order ['bonus_id'] = 0;
        }

        /* 配送方式 */
        if ($order ['shipping_id'] > 0) {
            $shipping = model('Shipping')->shipping_info($order ['shipping_id']);
            $order ['shipping_name'] = addslashes($shipping ['shipping_name']);
        }
		
        $order ['shipping_fee'] = $total ['shipping_fee'];
        $order ['insure_fee'] = $total ['shipping_insure'];
        /* 支付方式 */
        if ($order ['pay_id'] > 0) {
            $payment = model('Order')->payment_info($order ['pay_id']);
            $order ['pay_name'] = addslashes($payment ['pay_name']);
        }

        $order ['pay_fee'] = $total ['pay_fee'];
        $order ['cod_fee'] = $total ['cod_fee'];

		
        /* 商品包装 */
        if ($order ['pack_id'] > 0) {
            $pack = model('Order')->pack_info($order ['pack_id']);
            $order ['pack_name'] = addslashes($pack ['pack_name']);
        }
        $order ['pack_fee'] = $total ['pack_fee'];

        /* 祝福贺卡 */
        if ($order ['card_id'] > 0) {
            $card = model('Order')->card_info($order ['card_id']);
            $order ['card_name'] = addslashes($card ['card_name']);
        }
        $order ['card_fee'] = $total ['card_fee'];
        $order ['order_amount'] = number_format($total ['amount'], 2, '.', '');

        /* 如果全部使用余额支付，检查余额是否足够 */
        if ($payment ['pay_code'] == 'balance' && $order ['order_amount'] > 0) {
            if ($order ['surplus'] > 0) {    // 余额支付里如果输入了一个金额
                $order ['order_amount'] = $order ['order_amount'] + $order ['surplus'];
                $order ['surplus'] = 0;
            }
            if ($order ['order_amount'] > ($user_info ['user_money'] + $user_info ['credit_line'])) {
                show_message(L('balance_not_enough'));
            } else {
                $order ['surplus'] = $order ['order_amount'];
                $order ['order_amount'] = 0;
            }
        }

        /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
        if ($order ['order_amount'] <= 0) {
            $order ['order_status'] = OS_CONFIRMED;
            $order ['confirm_time'] = time();
            $order ['pay_status'] = PS_PAYED;
            $order ['pay_time'] = time();
            $order ['order_amount'] = 0;
        }

        $order ['integral_money'] = $total ['integral_money'];
        $order ['integral'] = $total ['integral'];



        $order ['from_ad'] = !empty($_SESSION ['from_ad']) ? $_SESSION ['from_ad'] : '0';
        $order ['referer'] = !empty($_SESSION ['referer']) ? addslashes($_SESSION ['referer']). 'Touch' : 'Touch';

        /* 记录扩展信息 */       
        $order ['extension_code'] = 'crowd_buy';
  

        $parent_id = M()->table('users')->field('parent_id')->where("user_id=".$_SESSION['user_id'])->getOne();
        $order ['parent_id'] = $parent_id;
		/* 插入众筹项目信息 */ 
		$order ['goods_id'] = $cart_goods['goods_id'];
		$order ['cp_id'] = $cart_goods['cp_id'];
		$order ['goods_name'] = $cart_goods['goods_name'];
		$order ['goods_number'] = $cart_goods['number'];
		$order ['goods_price'] = $cart_goods['shop_price'];
		
        /* 插入订单表 */
        $error_no = 0;
        do {
            $order ['order_sn'] = get_order_sn(); // 获取新订单号
            $new_order = model('Common')->filter_field('crowd_order_info', $order);
            $this->model->table('crowd_order_info')->data($new_order)->insert();
            $error_no = M()->errno();

            if ($error_no > 0 && $error_no != 1062) {
                die(M()->errorMsg());
            }
        } while ($error_no == 1062); // 如果是订单号重复则重新提交数据
        $new_order_id = M()->insert_id();
        $order ['order_id'] = $new_order_id;
		
		/* 统计方案售出数量 */
		/* $crowd_plan = $this->model->table('crowd_plan')->field('backey_num')->where("goods_id = '" . $_SESSION['goods_id'] . "' and cp_id = '" . $_SESSION['cp_id'] . "' ")->find();
		$backey_num = $crowd_plan['backey_num']+$cart_goods[number];			
		$where['goods_id'] = $_SESSION['goods_id'];
		$where['cp_id'] = $_SESSION['cp_id'];
		$datas['backey_num'] = $backey_num;
		$this->model->table('crowd_plan')->data($datas)->where($where)->update(); */

        /* 统计项目累计售出数量 */
		/* $goods = $this->model->table('crowd_goods')->field('buy_num')->where("goods_id = '" . $_SESSION['goods_id'] . "' ")->find();
		$buy_num = $goods['buy_num']+$cart_goods[number];			
		$where1['goods_id'] = $_SESSION['goods_id'];
		$data1['buy_num'] = $buy_num;
		$this->model->table('crowd_goods')->data($data1)->where($where1)->update();	 */
				
		
		/* 如果全部使用余额支付，检查余额是否足够 */
        if ($payment ['pay_code'] == 'balance' && $order ['surplus'] > 0) {
			//验证项目是否成功
			$crowd_goods = $this->model->table('crowd_goods')->field('sum_price')->where("goods_id = '" . $order['goods_id'] . "'  ")->find();
			$total_price = model('Crowdfunding')->crowd_buy_price($order['goods_id']);
			if($total_price >= $crowd_goods['sum_price']){
				$data['status'] = 1;
				$this->model->table('crowd_goods')->data($data)->where("goods_id = '" . $order['goods_id'] . "'  ")->update();			
			}
			
		}
		
		
		
        /* 处理余额、积分、红包 */
        if ($order ['user_id'] > 0 && $order ['surplus'] > 0) {
            model('ClipsBase')->log_account_change($order ['user_id'], $order ['surplus'] * (- 1), 0, 0, 0, sprintf(L('pay_order'), $order ['order_sn']));
			//付款更新众筹信息
			model('Crowdbuy')->update_crowd($order['order_id']);
			
        }
        if ($order ['user_id'] > 0 && $order ['integral'] > 0) {
            model('ClipsBase')->log_account_change($order ['user_id'], 0, 0, 0, $order ['integral'] * (- 1), sprintf(L('pay_order'), $order ['order_sn']));
        }

        if ($order ['bonus_id'] > 0 && $temp_amout > 0) {
            model('Order')->use_bonus($order ['bonus_id'], $new_order_id);
        }


        /* 给商家发邮件 */
        /* 增加是否给客服发送邮件选项 */
        if (C('send_service_email') && C('service_email') != '') {
            $tpl = model('Base')->get_mail_template('remind_of_new_order');
            $this->assign('order', $order);
            $this->assign('goods_list', $cart_goods);
            $this->assign('shop_name', C('shop_name'));
            $this->assign('send_date', date(C('time_format')));
            $content = ECTouch::$view->fetch('str:' . $tpl ['template_content']);
            send_mail(C('shop_name'), C('service_email'), $tpl ['template_subject'], $content, $tpl ['is_html']);
        }

        /* 如果需要，发短信 */
        if (C('sms_order_placed') == '1' && C('sms_shop_mobile') != '') {
            $sms = new EcsSms();
            $msg = $order ['pay_status'] == PS_UNPAYED ? L('order_placed_sms') : L('order_placed_sms') . '[' . L('sms_paid') . ']';
            $sms->send(C('sms_shop_mobile'), sprintf($msg, $order ['consignee'], $order ['mobile']), '', 13, 1);
        }
        /* 如果需要，微信通知 by wanglu */
        // if (method_exists('WechatController', 'do_oauth')) {
        //     $order_url = __HOST__ . url('user/order_detail', array('order_id' => $order ['order_id']));
        //     $order_url = urlencode(base64_encode($order_url));
        //     send_wechat_message('order_remind', '', $order['order_sn'] . L('order_effective'), $order_url, $order['order_sn']);
        // }
        /* 如果订单金额为0 处理虚拟卡 */
        if ($order ['order_amount'] <= 0) {
            $sql = "SELECT goods_id, goods_name, goods_number AS num FROM " . $this->model->pre . "cart WHERE is_real = 0 AND extension_code = 'virtual_card'" . " AND session_id = '" . SESS_ID . "' AND rec_type = '$flow_type'";
            $res = $this->model->query($sql);

            $virtual_goods = array();
            foreach ($res as $row) {
                $virtual_goods ['virtual_card'] [] = array(
                    'goods_id' => $row ['goods_id'],
                    'goods_name' => $row ['goods_name'],
                    'num' => $row ['num']
                );
            }

            if ($virtual_goods and $flow_type != CART_GROUP_BUY_GOODS) {
                /* 虚拟卡发货 */
                if (model('OrderBase')->virtual_goods_ship($virtual_goods, $msg, $order ['order_sn'], true)) {
                    /* 如果没有实体商品，修改发货状态，送积分和红包 */
                    $count = $this->model->table('order_goods')->field('COUNT(*)')->where("order_id = '$order[order_id]' " . " AND is_real = 1")->getOne();
                    if ($count <= 0) {
                        /* 修改订单状态 */
                        model('Users')->update_order($order ['order_id'], array(
                            'shipping_status' => SS_SHIPPED,
                            'shipping_time' => time()
                        ));

                        /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                        if ($order ['user_id'] > 0) {
                            /* 取得用户信息 */
                            $user = model('Order')->user_info($order ['user_id']);

                            /* 计算并发放积分 */
                            $integral = model('Order')->integral_to_give($order);
                            model('ClipsBase')->log_account_change($order ['user_id'], 0, 0, intval($integral ['rank_points']), intval($integral ['custom_points']), sprintf(L('order_gift_integral'), $order ['order_sn']));

                            /* 发放红包 */
                            model('Order')->send_order_bonus($order ['order_id']);
                        }
                    }
                }
            }
        }


        /* 插入支付日志 */
        $order ['log_id'] = model('ClipsBase')->insert_pay_log($new_order_id, $order ['order_amount'], PAY_ORDER);

        /* 取得支付信息，生成支付代码 */
        if ($order ['order_amount'] > 0) {
            $payment = model('Order')->payment_info($order ['pay_id']);

            include_once (ROOT_PATH . 'plugins/payment/' . $payment ['pay_code'] . '.php');

            $pay_obj = new $payment ['pay_code'] ();

            $pay_online = $pay_obj->get_code($order, unserialize_config($payment ['pay_config']));

            $order ['pay_desc'] = $payment ['pay_desc'];

            $this->assign('pay_online', $pay_online);
        }
        if (!empty($order ['shipping_name'])) {
            $order ['shipping_name'] = trim(stripcslashes($order ['shipping_name']));
        }
        // 如果是银行汇款或货到付款 则显示支付描述
        if ($payment['pay_code'] == 'bank' || $payment['pay_code'] == 'cod'){
            if (empty($order ['pay_name'])) {
                $order ['pay_name'] = trim(stripcslashes($payment ['pay_name']));
            }
            $this->assign('pay_desc',$order['pay_desc']);
        }
        // 货到付款不显示
        if ($payment ['pay_code'] != 'balance') {
            /* 生成订单后，修改支付，配送方式 */

            // 支付方式
            $payment_list = model('Order')->available_payment_list(0);
            if (isset($payment_list)) {
                foreach ($payment_list as $key => $payment) {

                    /* 如果有易宝神州行支付 如果订单金额大于300 则不显示 */
                    if ($payment ['pay_code'] == 'yeepayszx' && $total ['amount'] > 300) {
                        unset($payment_list [$key]);
                    }
                    // 过滤掉当前的支付方式
                    if ($payment ['pay_id'] == $order ['pay_id']) {
                        unset($payment_list [$key]);
                    }
                    /* 如果有余额支付 */
                    if ($payment ['pay_code'] == 'balance') {
                        /* 如果未登录，不显示 */
                        if ($_SESSION ['user_id'] == 0) {
                            unset($payment_list [$key]);
                        } else {
                            if ($_SESSION ['flow_order'] ['pay_id'] == $payment ['pay_id']) {
                                $this->assign('disable_surplus', 1);
                            }
                        }
                    }
                    // 如果不是微信浏览器访问并且不是微信会员 则不显示微信支付
                    if ($payment ['pay_code'] == 'wxpay' && !is_wechat_browser() && empty($_SESSION['openid'])) {
                        unset($payment_list [$key]);
                    }
                    // 兼容过滤ecjia支付方式
                    if (substr($payment['pay_code'], 0 , 4) == 'pay_') {
                        unset($payment_list[$key]);
                    }
                }
            }
            $this->assign('payment_list', $payment_list);
            $this->assign('pay_code', 'no_balance');
        }



        /* 订单信息 */
        $this->assign('order', $order);

        $this->assign('total', $total);
        $this->assign('goods_list', $cart_goods);
        $this->assign('order_submit_back', sprintf(L('order_submit_back'), L('back_home'), L('goto_user_center'))); // 返回提示

        user_uc_call('add_feed', array($order ['order_id'], BUY_GOODS)); // 推送feed到uc
        unset($_SESSION ['flow_consignee']); // 清除session中保存的收货人信息
        unset($_SESSION ['flow_order']);
        unset($_SESSION ['direct_shopping']);
		// 清除session中保存项目信息
		unset($_SESSION['goods_id']); 
        unset($_SESSION['cp_id']);
        unset($_SESSION['number']);

        $this->assign('currency_format', C('currency_format'));
        $this->assign('integral_scale', C('integral_scale'));
        $this->assign('step', ACTION_NAME);

        $this->assign('title', L('order_submit'));
		 
		$this->display('crowd/raise_done.html');
	 }

   

}
