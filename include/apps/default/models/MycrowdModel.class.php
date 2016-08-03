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

class MycrowdModel extends CommonModel {

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
     * 获取方案最低价格     
     */
	function plan_min_price($goods_id = 0){		
		$sql = 'SELECT min(shop_price) as price '.'FROM '
		. $this->model->pre . 'crowd_plan ' . "WHERE status = 1 and goods_id = '$goods_id'  ";
        $res = $this->row($sql);
        return $res['price'];
	}
	
	
	/**
     * 获取关注众筹     
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
