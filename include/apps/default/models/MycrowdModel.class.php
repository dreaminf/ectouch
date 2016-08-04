<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：IndexModel.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTOUCH 我的众筹
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class MycrowdModel extends BaseModel {

	/**
     * 获取推荐众筹     
     */
	function recom_list(){		
		$sql = 'SELECT goods_id, cat_id, goods_name, goods_img, sum_price, total_price, start_time, time '.'FROM '
		. $this->pre . 'crowd_goods ' . "WHERE is_verify = 1 and recommend = 1 order by sort_order DESC ";
        $res = $this->query($sql);

		$goods = array();
        foreach ($res AS $key => $row) {
            $goods[$key]['id'] = $row['goods_id'];
			$goods[$key]['cat_id'] = $row['cat_id'];
            $goods[$key]['goods_name'] = $row['goods_name'];
			$goods[$key]['time'] = $row['time'];
            $goods[$key]['like_num'] = $row['like_num'];
            $goods[$key]['buy_num'] = model('Crowdfunding')->crowd_buy_num($row['goods_id']);
			$goods[$key]['start_time'] =floor((gmtime()-$row['start_time'])/86400);
			$goods[$key]['sum_price'] = $row['sum_price'];
            $goods[$key]['total_price'] = model('Crowdfunding')->crowd_buy_price($row['goods_id']);
            $goods[$key]['goods_img'] = 'data/attached/crowdimage/'.$row['goods_img'];
            $goods[$key]['url'] = url('Crowdfunding/goods_info', array('id' => $row['goods_id']));
			$goods[$key]['bar'] = $goods[$key]['total_price']*100/$row['sum_price'];
			$goods[$key]['bar'] = round($goods[$key]['bar'],1); //计算百分比
			$goods[$key]['min_price'] = $this->plan_min_price($row['goods_id']); //获取方案最低价格
        }
        return $goods;
	}
	
	
	
	/**
     * 获取关注众筹列表    
     */
	function like_list($user_id = 0, $type= 0){	

		switch($type){
            case 1:
            $where = " ";                  //全部
            break;
            case 2:
            $where = " AND g.status < 1";  //进行中
            break;
            case 3:
            $where = " AND g.status = 1";  //已成功
            break;
            case 4:
            $where = " AND g.status = 2";  //已失败
            break;
        }

	
		 $sql = 'SELECT g.goods_id, g.cat_id, g.goods_name, g.goods_img, g.sum_price, g.total_price, g.start_time, g.time  '.'FROM '
		. $this->pre . 'crowd_goods as g left join ' . $this->pre  ."crowd_like as cl" . " on g.goods_id=cl.goods_id " . " WHERE g.is_verify = 1 $where and cl.user_id = '$user_id'  order by g.sort_order DESC ";

        $res = $this->query($sql);
		$goods = array();
        foreach ($res AS $key => $row) {
            $goods[$key]['id'] = $row['goods_id'];
			$goods[$key]['cat_id'] = $row['cat_id'];
            $goods[$key]['goods_name'] = $row['goods_name'];
			$goods[$key]['time'] = $row['time'];
            $goods[$key]['like_num'] = $row['like_num'];
            $goods[$key]['buy_num'] = model('Crowdfunding')->crowd_buy_num($row['goods_id']);
			$goods[$key]['start_time'] =floor((gmtime()-$row['start_time'])/86400);
			$goods[$key]['sum_price'] = $row['sum_price'];
            $goods[$key]['total_price'] = model('Crowdfunding')->crowd_buy_price($row['goods_id']);
            $goods[$key]['goods_img'] = 'data/attached/crowdimage/'.$row['goods_img'];
            $goods[$key]['url'] = url('Crowdfunding/goods_info', array('id' => $row['goods_id']));
			$goods[$key]['bar'] = $goods[$key]['total_price']*100/$row['sum_price'];
			$goods[$key]['bar'] = round($goods[$key]['bar'],1); //计算百分比
			$goods[$key]['min_price'] = $this->plan_min_price($row['goods_id']); //获取方案最低价格
        }
        return $goods;
	}
	
	
	/**
     * 我支持的众筹列表     
     */
	function crowd_buy_list($user_id = 0, $type= 0){	

		switch($type){
            case 1:
            $where = " ";                  //全部
            break;
            case 2:
            $where = " AND g.status < 1";  //进行中
            break;
            case 3:
            $where = " AND g.status = 1";  //已成功
            break;
            case 4:
            $where = " AND g.status = 2";  //已失败
            break;
        }

		$sql = "SELECT g.goods_id, g.cat_id, g.goods_name, g.goods_img, g.sum_price, g.total_price, g.start_time, g.time   FROM ". $this->pre ."order_info as o left join  ". $this->pre ."order_goods as og on o.order_id = og.order_id". " left join " .$this->pre."crowd_goods as g on g.goods_id = og.goods_id". " WHERE o.user_id = '$user_id' $where and  o.extension_code = 'crowd_buy' ";
		
        $res = $this->query($sql);
		$goods = array();
        foreach ($res AS $key => $row) {
            $goods[$key]['id'] = $row['goods_id'];
			$goods[$key]['cat_id'] = $row['cat_id'];
            $goods[$key]['goods_name'] = $row['goods_name'];
			$goods[$key]['time'] = $row['time'];
            $goods[$key]['like_num'] = $row['like_num'];
            $goods[$key]['buy_num'] = model('Crowdfunding')->crowd_buy_num($row['goods_id']);
			$goods[$key]['start_time'] =floor((gmtime()-$row['start_time'])/86400);
			$goods[$key]['sum_price'] = $row['sum_price'];
            $goods[$key]['total_price'] = model('Crowdfunding')->crowd_buy_price($row['goods_id']);
            $goods[$key]['goods_img'] = 'data/attached/crowdimage/'.$row['goods_img'];
            $goods[$key]['url'] = url('Crowdfunding/goods_info', array('id' => $row['goods_id']));
			$goods[$key]['bar'] = $goods[$key]['total_price']*100/$row['sum_price'];
			$goods[$key]['bar'] = round($goods[$key]['bar'],1); //计算百分比
			$goods[$key]['min_price'] = $this->plan_min_price($row['goods_id']); //获取方案最低价格
        }
        return $goods;
	}
	
	/**
     * 获取详情    
     */
	function crowd_user_orders($user_id, $pay = 1, $num = 10, $start = 0){		
		/* 取得订单列表 */

        $arr = array();
        if ($pay == 1) {
			 // 全部订单
            $pay = '';
        } elseif($pay == 2) {
            // 未付款 但不包含已取消、无效、退货订单的订单
            $pay = 'and pay_status = ' . PS_UNPAYED . ' and order_status not in(' . OS_CANCELED . ','. OS_INVALID .','. OS_RETURNED .')';
        }elseif($pay == 3) {
            // //代发货
            $pay = 'and pay_status = ' . PS_PAYED . ' and shipping_status ='. SS_UNSHIPPED  ;
        }elseif($pay == 4) {
            // //待收货
            $pay = 'and pay_status = ' . PS_PAYED . ' and shipping_status ='. SS_SHIPPED  ;
        }else{
			// 已完结
            $pay = 'and pay_status = ' . PS_PAYED . ' and shipping_status ='. SS_RECEIVED  ;
        }

        $sql = "SELECT order_id, order_sn, shipping_id, order_status, shipping_status, pay_status, add_time,order_amount, " .
                "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee " .
                " FROM " . $this->pre .
                "order_info WHERE user_id = '$user_id' and extension_code = 'crowd_buy' " . $pay . " ORDER BY add_time DESC LIMIT $start , $num";
        $res = M()->query($sql);
        foreach ($res as $key => $value) {
            if ($value['order_status'] == OS_UNCONFIRMED) {
                $value['handler'] = "<a href=\"" . url('mycrowd/cancel_order', array('order_id' => $value['order_id'])) . "\"class=\" btn-default \" onclick=\"if (!confirm('取消订单')) return false;\">" .'取消' . "</a>";
            } else if ($value['order_status'] == OS_SPLITED) {
                /* 对配送状态的处理 */
                if ($value['shipping_status'] == SS_SHIPPED) {
                    @$value['handler'] = "<a href=\"" . url('user/affirm_received', array('order_id' => $value['order_id'])) . "\" onclick=\"if (!confirm('" . L('confirm_received') . "')) return false;\">" . L('received') . "</a>";
                } elseif ($value['shipping_status'] == SS_RECEIVED) {
                    @$value['handler'] = '<span style="color:red">' . L('ss_received') . '</span>';
                } else {
                    if ($value['pay_status'] == PS_UNPAYED) {
                        @$value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\">" . L('pay_money') . "</a>";
                    } else {
                        @$value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\">" . L('view_order') . "</a>";
                    }
                }
            } else {
                $value['handler'] = '<span>' . L('os.' . $value['order_status']) . '</span>';
            }

            $value['shipping_status'] = ($value['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $value['shipping_status'];
            $value['status'] = L('os.' . $value['order_status']) . ',' . L('ps.' . $value['pay_status']) . ',' . L('ss.' . $value['shipping_status']);
			
			// 订单详情
			$order = model('Users')->get_order_detail($value['order_id'], $user_id);

			
			$sql = "SELECT og.goods_name,og.goods_id,og.goods_number,og.goods_price,cp.name FROM ". $this->pre ."order_goods as og left join  ". $this->pre ."crowd_plan as cp on og.cp_id = cp.cp_id " . " WHERE og.order_id = '".$value['order_id']."' ";
			$res = $this->row($sql);
			
			

            $arr[] = array(
                'order_id' => $value['order_id'],
                'order_sn' => $value['order_sn'],
                'img' => 'data/attached/crowdimage/'. $this->order_thumb($value['order_id']),
                'order_time' => local_date(C('time_format'), $value['add_time']),
                'status' => $value['status'],
                'shipping_id' => $value['shipping_id'],
                'total_fee' => price_format($value['total_fee'], false),
                //'url' => url('user/order_detail', array('order_id' => $value['order_id'])),
                'goods_count' => model('Users')->get_order_goods_count($value['order_id']),
                'handler' => $value['handler'],
				'order_status' => $value['order_status'],        //订单状态
				'pay_status' => $value['pay_status'],            //支付状态
				'shipping_status' => $value['shipping_status'],  //配送状态
				'goods_name' => $res['goods_name'],
				'goods_number' => $res['goods_number'],
				'goods_price' => $res['goods_price'],
				'goods_id' => $res['goods_id'],
				'name' => $res['name'],
				'pay_online' => $order['pay_online'],            //支付按钮
				'url' => url('Crowdfunding/goods_info', array('id' => $res['goods_id']))
            );

        }

        return $arr;
	}
	
	/**
    * 获取订单商品的数量
    * @param type $order_id
    * @return type
    */
    function crowd_orders_num($user_id = 0, $status = 0) {
		switch($status){
            case 1:
            $where = " ";       //全部订单
            break;
            case 2:
            $where = 'and pay_status = ' . PS_UNPAYED . ' and order_status not in(' . OS_CANCELED . ','. OS_INVALID .','. OS_RETURNED .')';  		//待支付订单
            break;
            case 3:
            $where = 'and pay_status = ' . PS_PAYED . ' and shipping_status ='. SS_UNSHIPPED  ;		//代发货
            break;
            case 4:
            $where = 'and pay_status = ' . PS_PAYED . ' and shipping_status ='. SS_SHIPPED  ;	    //待收货
            break;
			case 5:
            $where = 'and pay_status = ' . PS_PAYED . ' and shipping_status ='. SS_RECEIVED  ;		//已完结
            break;
        }

		$sql = "SELECT count(order_id) as num  FROM ". $this->pre ."order_info "." WHERE user_id = '".$user_id."' $where  AND extension_code = 'crowd_buy' ";
        $res = $this->row($sql);
        return $res['num'];
    }
	
	
	
	/**
    * 获取订单商品的缩略图
    * @param type $order_id
    * @return type
    */
    function order_thumb($order_id) {

        $arr = $this->row("SELECT g.goods_img FROM " . $this->model->pre . "order_goods as og left join " . $this->model->pre . "crowd_goods g on og.goods_id = g.goods_id WHERE og.order_id = " . $order_id . " limit 1");		
        return $arr['goods_img'];
    }
	
	/**
     * 获取方案最低价格     
     */
	function plan_min_price($goods_id = 0){		
		$sql = 'SELECT min(shop_price) as price '.'FROM '
		. $this->model->pre . 'crowd_plan ' . "WHERE status = 1 and goods_id = '$goods_id'  ";
        $res = $this->row($sql);
        return $res['price'];
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
