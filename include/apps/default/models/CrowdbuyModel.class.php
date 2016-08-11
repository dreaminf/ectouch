<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：IndexModel.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTOUCH 购物流程控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class CrowdbuyModel extends CommonModel {
	
	/**
    * 众筹项目信息
    */
	function cart_crowd_goods($goods_id, $cp_id, $number){
	
		$sql = 'SELECT cg.goods_id, cg.goods_name, cg.goods_img, cg.sum_price, cg.start_time,time,cp.cp_id, cp.shop_price, cp.name '.'FROM '. $this->pre . 'crowd_goods as cg left join ' . $this->pre . 'crowd_plan as cp' .' on cp.goods_id=cg.goods_id '.  "WHERE cg.is_verify = 1 and cp.goods_id = '$goods_id' and cp.cp_id = '$cp_id' ";

        $row = $this->row($sql);
        if ($row !== false) {
            $row['goods_id'] = $row['goods_id'];
            $row['goods_name'] = $row['goods_name'];
            $row['buy_num'] = model('Crowdfunding')->crowd_buy_num($row['goods_id']);
			$row['start_time'] =floor((gmtime()-$row['start_time'])/86400);
			$row['sum_price'] = $row['sum_price'];
			$row['total_price'] = model('Crowdfunding')->crowd_buy_price($row['goods_id']);
            $row['goods_img'] = $row['goods_img'];
            $row['url'] = url('Crowdfunding/goods_info', array('id' => $row['goods_id']));
			$row['bar'] = $row['total_price']*100/$row['sum_price'];
			$row['bar'] = round($row['bar'],1); //计算百分比sum_price
			$row['formated_subtotal'] = $row['shop_price']*$number;//商品总价
			$row['number'] = $number;//购买数量
			
            return $row;
        } else {
            return false;
        }
	
	
	}
	
	
	function crowd_order_fee($order, $goods, $consignee){
		

        $total = array('real_goods_count' => 0,
            'gift_amount' => 0,
            'goods_price' => 0,
            'market_price' => 0,
            'discount' => 0,
            'pack_fee' => 0,
            'card_fee' => 0,
            'shipping_fee' => 0,
            'shipping_insure' => 0,
            'integral_money' => 0,
            'bonus' => 0,
            'surplus' => 0,
            'cod_fee' => 0,
            'pay_fee' => 0,
            'tax' => 0);
        $weight = 0;
		$goods = array(0=>$goods);
	
        /* 商品总价 */
        foreach ($goods AS $val) {	
            $total['goods_price'] += $val['shop_price'] * $val['number'];
        }

        /* 配送费用 */
        $shipping_cod_fee = NULL;

        if ($order['shipping_id'] > 0 ) {
            $region['country'] = $consignee['country'];
            $region['province'] = $consignee['province'];
            $region['city'] = $consignee['city'];
            $region['district'] = $consignee['district'];
            $shipping_info = model('Shipping')->shipping_area_info($order['shipping_id'], $region);

            if (!empty($shipping_info)) {
                if ($order['extension_code'] == 'group_buy') {
                    $weight_price = model('Order')->cart_weight_price(CART_GROUP_BUY_GOODS);
                } else {
                    $weight_price = model('Order')->cart_weight_price();
                }

                // 查看购物车中是否全为免运费商品，若是则把运费赋为零
                $sql = 'SELECT count(*) as count FROM ' . $this->pre . "cart WHERE  `session_id` = '" . SESS_ID . "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0";
                $res = $this->row($sql);
                $shipping_count = $res['count'];

                $total['shipping_fee'] = ($shipping_count == 0 AND $weight_price['free_shipping'] == 1) ? 0 : shipping_fee($shipping_info['shipping_code'], $shipping_info['configure'], $weight_price['weight'], $total['goods_price'], $weight_price['number']);

                if (!empty($order['need_insure']) && $shipping_info['insure'] > 0) {
                    $total['shipping_insure'] = shipping_insure_fee($shipping_info['shipping_code'], $total['goods_price'], $shipping_info['insure']);
                } else {
                    $total['shipping_insure'] = 0;
                }

                if ($shipping_info['support_cod']) {
                    $shipping_cod_fee = $shipping_info['pay_fee'];
                }
            }
        }

        $total['shipping_fee_formated'] = price_format($total['shipping_fee'], false);
        $total['shipping_insure_formated'] = price_format($total['shipping_insure'], false);

        // 购物车中的商品能享受红包支付的总额
        $bonus_amount = model('Order')->compute_discount_amount();
        // 红包和积分最多能支付的金额为商品总额
        $max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;

        /* 计算订单总额 */
        if ($order['extension_code'] == 'group_buy' && $group_buy['deposit'] > 0) {
            $total['amount'] = $total['goods_price'];
        } else {
            $total['amount'] = $total['goods_price'] - $total['discount'] + $total['tax'] + $total['pack_fee'] + $total['card_fee'] +
                    $total['shipping_fee'] + $total['shipping_insure'] + $total['cod_fee'];

            // 减去红包金额
            $use_bonus = min($total['bonus'], $max_amount); // 实际减去的红包金额
            if (isset($total['bonus_kill'])) {
                $use_bonus_kill = min($total['bonus_kill'], $max_amount);
                $total['amount'] -= $price = number_format($total['bonus_kill'], 2, '.', ''); // 还需要支付的订单金额
            }

            $total['bonus'] = $use_bonus;
            $total['bonus_formated'] = price_format($total['bonus'], false);

            $total['amount'] -= $use_bonus; // 还需要支付的订单金额
            $max_amount -= $use_bonus; // 积分最多还能支付的金额
        }

        /* 余额 */
        $order['surplus'] = $order['surplus'] > 0 ? $order['surplus'] : 0;
        if ($total['amount'] > 0) {
            if (isset($order['surplus']) && $order['surplus'] > $total['amount']) {
                $order['surplus'] = $total['amount'];
                $total['amount'] = 0;
            } else {
                $total['amount'] -= floatval($order['surplus']);
            }
        } else {
            $order['surplus'] = 0;
            $total['amount'] = 0;
        }
        $total['surplus'] = $order['surplus'];
        $total['surplus_formated'] = price_format($order['surplus'], false);

        /* 积分 */
        $order['integral'] = $order['integral'] > 0 ? $order['integral'] : 0;
        if ($total['amount'] > 0 && $max_amount > 0 && $order['integral'] > 0) {
            $integral_money = value_of_integral($order['integral']);

            // 使用积分支付
            $use_integral = min($total['amount'], $max_amount, $integral_money); // 实际使用积分支付的金额
            $total['amount'] -= $use_integral;
            $total['integral_money'] = $use_integral;
            $order['integral'] = integral_of_value($use_integral);
        } else {
            $total['integral_money'] = 0;
            $order['integral'] = 0;
        }
        $total['integral'] = $order['integral'];
        $total['integral_formated'] = price_format($total['integral_money'], false);

        /* 保存订单信息 */
        $_SESSION['flow_order'] = $order;

        $se_flow_type = isset($_SESSION['flow_type']) ? $_SESSION['flow_type'] : '';

        /* 支付费用 */
        if (!empty($order['pay_id']) && ($total['real_goods_count'] > 0 || $se_flow_type != CART_EXCHANGE_GOODS)) {
            $total['pay_fee'] = pay_fee($order['pay_id'], $total['amount'], $shipping_cod_fee);
        }

        $total['pay_fee_formated'] = price_format($total['pay_fee'], false);

        $total['amount'] += $total['pay_fee']; // 订单总额累加上支付费用
        $total['amount_formated'] = price_format($total['amount'], false);

        /* 取得可以得到的积分和红包 */
        if ($order['extension_code'] == 'group_buy') {
            $total['will_get_integral'] = $group_buy['gift_integral'];
        } elseif ($order['extension_code'] == 'exchange_goods') {
            $total['will_get_integral'] = 0;
        } else {
            $total['will_get_integral'] = model('Order')->get_give_integral($goods);
        }
        $total['will_get_bonus'] = $order['extension_code'] == 'exchange_goods' ? 0 : price_format(model('Order')->get_total_bonus(), false);
        $total['formated_goods_price'] = price_format($total['goods_price'], false);
        $total['formated_market_price'] = price_format($total['market_price'], false);
        $total['formated_saving'] = price_format($total['saving'], false);

        if ($order['extension_code'] == 'exchange_goods') {
            $sql = 'SELECT SUM(eg.exchange_integral) ' .
                    'as sum FROM ' . $this->pre . 'cart AS c,' . $this->pre . 'exchange_goods AS eg ' .
                    "WHERE c.goods_id = eg.goods_id AND c.session_id= '" . SESS_ID . "' " .
                    "  AND c.rec_type = '" . CART_EXCHANGE_GOODS . "' " .
                    '  AND c.is_gift = 0 AND c.goods_id > 0 ' .
                    'GROUP BY eg.goods_id';
            $res = $this->row($sql);
            $exchange_integral = $res['sum'];
            $total['exchange_integral'] = $exchange_integral;
        }

        return $total;
		
		
		
	}
	
	
	
	/**
     * 获得订单信息
     *
     * @access  private
     * @return  array
     */
    function crowd_flow_order_info() {
        $order = isset($_SESSION['flow_order']) ? $_SESSION['flow_order'] : array();

        /* 初始化配送和支付方式 */
        if (!isset($order['shipping_id']) || !isset($order['pay_id'])) {
            /* 如果还没有设置配送和支付 */
            if ($_SESSION['user_id'] > 0) {
                /* 用户已经登录了，则获得上次使用的配送和支付 */
                $arr = $this->crowd_last_shipping_and_payment();

                if (!isset($order['shipping_id'])) {
                    $order['shipping_id'] = $arr['shipping_id'];
                }
                if (!isset($order['pay_id'])) {
                    $order['pay_id'] = $arr['pay_id'];
                }
            } else {
                if (!isset($order['shipping_id'])) {
                    $order['shipping_id'] = 0;
                }
                if (!isset($order['pay_id'])) {
                    $order['pay_id'] = 0;
                }
            }
        }

        if (!isset($order['pack_id'])) {
            $order['pack_id'] = 0;  // 初始化包装
        }
        if (!isset($order['card_id'])) {
            $order['card_id'] = 0;  // 初始化贺卡
        }
        if (!isset($order['bonus'])) {
            $order['bonus'] = 0;    // 初始化红包
        }
        if (!isset($order['integral'])) {
            $order['integral'] = 0; // 初始化积分
        }
        if (!isset($order['surplus'])) {
            $order['surplus'] = 0;  // 初始化余额
        }

        /* 扩展信息 */
        if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS) {
            $order['extension_code'] = $_SESSION['extension_code'];
            $order['extension_id'] = $_SESSION['extension_id'];
        }

        return $order;
    }
	
	
	 /**
     * 获得上一次用户采用的支付和配送方式
     *
     * @access  public
     * @return  void
     */
    function crowd_last_shipping_and_payment() {
        $sql = "SELECT shipping_id, pay_id " .
                " FROM " . $this->pre .
                "crowd_order_info WHERE user_id = '$_SESSION[user_id]' " .
                " ORDER BY order_id DESC LIMIT 1";
        $row = $this->row($sql);

        if (empty($row)) {
            /* 如果获得是一个空数组，则返回默认值 */
            $row = array('shipping_id' => 0, 'pay_id' => 0);
        }

        return $row;
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
