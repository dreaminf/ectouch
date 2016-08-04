<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：IndexModel.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTOUCH 众筹分类商品列表
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class CrowdfundingModel extends CommonModel {
	/**
     * 获取当前分类及其子分类 
     * @param  $type
     * @param  $limit
     * @param  $start
     */
	function category_all(){		
		/* 获取当前分类及其子分类 */
		 $sql = 'SELECT c.cat_id,c.cat_name,c.parent_id,c.is_show ' .
				'FROM ' . $this->pre . 'crowd_category as c ' .
				"WHERE c.parent_id = 0 AND c.is_show = 1 ORDER BY c.sort_order ASC, c.cat_id ASC";

		$res = $this->query($sql);
		foreach ($res AS $row) {
			if ($row['is_show']) {
				$cat_arr[$row['cat_id']]['id'] = $row['cat_id'];
				$cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
				if (isset($row['cat_id']) == isset($row['parent_id'])) {
					$cat_arr[$row['cat_id']]['cat_id'] = $this->get_child_trees($row['cat_id']);
				}
			}
		}

		return $cat_arr;
	}
	
	/* 获取当前分类及其子分类 */
	function get_child_trees($tree_id = 0) {
		$three_arr = array();
		$sql = 'SELECT count(*) FROM '. $this->pre . "crowd_category WHERE parent_id = '$tree_id' AND is_show = 1 ";
		if ($this->row($sql) || $tree_id == 0) {
			$child_sql = 'SELECT c.cat_id, c.cat_name, c.parent_id, c.is_show ' .
					'FROM ' . $this->pre . 'crowd_category as c ' .
					" WHERE c.parent_id = '$tree_id' AND c.is_show = 1 GROUP BY c.cat_id ORDER BY c.sort_order ASC, c.cat_id ASC";
			$res = $this->query($child_sql);
			foreach ($res AS $row) {
				if ($row['is_show']) {
					$three_arr[$row['cat_id']]['id'] = $row['cat_id'];
					$three_arr[$row['cat_id']]['name'] = $row['cat_name'];
				}
				if (isset($row['cat_id']) != NULL) {
					$three_arr[$row['cat_id']]['cat_id'] = $this->get_child_trees($row['cat_id']);
				}
			}
		}
		return $three_arr;
	}
	/* 获取当前项目信息 */
	function crowd_goods_info($goods_id = 0) {
		$sql = 'SELECT goods_id, cat_id, goods_name, goods_img, sum_price, total_price, start_time,time,shiping_time,gallery_img, like_num, buy_num '.'FROM '. $this->pre . 'crowd_goods ' . "WHERE is_verify = 1 and goods_id = '$goods_id' ";
        $row = $this->row($sql);
        if ($row !== false) {
            $row['goods_id'] = $row['goods_id'];
			$row['cat_id'] = $row['cat_id'];
            $row['goods_name'] = $row['goods_name'];
            $row['like_num'] = $row['like_num'];
			$row['gallery_img'] = $row['gallery_img'];
            $row['buy_num'] = $this->crowd_buy_num($row['goods_id']);
			$row['start_time'] =floor((gmtime()-$row['start_time'])/86400);
			$row['time'] = $row['time'] - $row['start_time'];
			$row['shiping_time'] = local_date(C('time_format'), $row['shiping_time']);
			$row['sum_price'] = $row['sum_price'];
			$row['total_price'] = $this->crowd_buy_price($row['goods_id']);
            $row['goods_img'] = 'data/attached/crowdimage/'.$row['goods_img'];
            $row['url'] = url('Crowdfunding/goods_info', array('id' => $row['goods_id']));
			$row['bar'] = $row['total_price']*100/$row['sum_price'];
			$row['bar'] = round($row['bar'],1); //计算百分比

            return $row;
        } else {
            return false;
        }
		
		
	}
	
	/* 获取当前项目购买人数量*/
	function crowd_buy_num($goods_id = 0) {
		$sql = "SELECT count(g.rec_id) as num  FROM ". $this->pre ."order_info as o left join  ". $this->pre ."order_goods as g on o.order_id = g.order_id". " WHERE g.goods_id = '".$goods_id."' AND o.extension_code = 'crowd_buy' ";
        $res = $this->row($sql);
		return $res['num'];
	}
	
	/* 获取当前项目累计金额*/
	function crowd_buy_price($goods_id = 0) {
		$sql = "SELECT sum(g.goods_price) as price FROM ". $this->pre ."order_info as o left join  ". $this->pre ."order_goods as g on o.order_id = g.order_id". " WHERE g.goods_id = '".$goods_id."' AND o.extension_code = 'crowd_buy' ";
		$res = $this->row($sql);
        /* $res = $this->query($sql);
		foreach($res as $row){
			$price += $row['goods_price'];
		} */
		return $res['price'];
	}
	
	
	
	/* 获取当前项目方案 */
	function crowd_goods_paln($goods_id = 0) {
		$sql = 'SELECT cp_id, name, goods_id, shop_price, number, backey_num, cp_img, return_time '.'FROM '
		. $this->model->pre . 'crowd_plan ' . "WHERE status = 1 and goods_id = '$goods_id' order by sort_order ASC ";
        $res = $this->query($sql);
		
		$plan = array();
        foreach ($res AS $key => $row) {
            $plan[$key]['cp_id'] = $row['cp_id'];
			$plan[$key]['name'] = $row['name'];
            $plan[$key]['goods_id'] = $row['goods_id'];
			$plan[$key]['shop_price'] = price_format($row['shop_price']);
            $plan[$key]['number'] = $row['number'];
            $plan[$key]['backey_num'] = $row['backey_num'];
			$plan[$key]['surplus_num'] = $row['number']-$row['backey_num'];
            $plan[$key]['cp_img'] = 'data/attached/crowdimage/'.$row['cp_img'];           
        }
        return $plan;
		
	}
	
	/* 获取当前项目评论 */
	function crowd_comment($goods_id = 0) {
		$sql = "SELECT id,user_id,user_name,goods_id,content,add_time FROM ". $this->model->pre ."crowd_comment WHERE ".
               "goods_id = '".$goods_id."' AND status = 1 and parent_id = 0  ORDER BY id DESC LIMIT 0,3";
  
        $comment_list = $this->model->query($sql);
        foreach($comment_list as $k => $v){
			$comment_list[$k]['add_time'] = local_date(C('time_format'), $v['add_time']);
			$comment_list[$k]['name'] =  $v['user_name'];
			$comment_list[$k]['avatar'] = '';
			$wechat_user = $this->model->table('wechat_user')->where("ect_uid=".$v['user_id'])->field('nickname,headimgurl')->find();
			if(!empty($wechat_user)){
				$comment_list[$k]['name'] =  $wechat_user['nickname'];
				$comment_list[$k]['avatar'] = $wechat_user['headimgurl'];
			}
        }
        return $comment_list;
	}
	/**
     * 获取众筹项目的评价详情shuliang 
     */
	function crowd_comment_info($id){
		
		switch($type){
            case 1:
            $where = " AND rank > 0";
            break;
            case 2:
            $where = " AND rank IN (4,5)";
            break;
            case 3:
            $where = " AND rank IN (2,3)";
            break;
            case 4:
            $where = " AND rank IN (0,1)";
            break;
        }
		
		$sql = 'SELECT COUNT(*) as count FROM ' . $this->pre .
                "crowd_comment WHERE goods_id = '$id' AND rank > 0  AND status = 1 AND parent_id = 0" .
                ' ORDER BY id DESC';
        $result = $this->row($sql);
        $info['sum_count'] = $result['count'];
		
		
		$sql = 'SELECT COUNT(*) as count FROM ' . $this->pre .
                "crowd_comment WHERE goods_id = '$id'   AND status = 1 AND parent_id = 0" .
                ' ORDER BY id DESC';
        $result = $this->row($sql);
        $info['count'] = $result['count'];

        $sql = 'SELECT COUNT(*) as count FROM ' . $this->pre .
                "crowd_comment WHERE goods_id = '$id'  AND (rank= 5 OR rank = 4) AND status = 1 AND parent_id = 0" .
                ' ORDER BY id DESC';
        $result = $this->row($sql);
        $favorable = $result['count'];

        $sql = 'SELECT COUNT(*) as count FROM ' . $this->pre .
                "crowd_comment WHERE goods_id = '$id'  AND status = 1 AND parent_id = 0 AND(rank = 2 OR rank = 3)" .
                ' ORDER BY id DESC';
        $result = $this->row($sql);
        $medium = $result['count'];

        $sql = 'SELECT COUNT(*) as count FROM ' . $this->pre .
                "crowd_comment WHERE goods_id = '$id' AND status = 1 AND parent_id = 0 AND rank = 1 " .
                ' ORDER BY id DESC';
        $result = $this->row($sql);
        $bad = $result['count'];

        $info['favorable_count'] = $favorable; //好评数量
        $info['medium_count'] = $medium; //中评数量
        $info['bad_count'] = $bad; //差评数量
        if ($info['count'] > 0) {
            $info['favorable'] = 0;
            if ($favorable) {
                $info['favorable'] = round(($favorable / $info['count']) * 100);  //好评率
            }
            $info['medium'] = 0;
            if ($medium) {
                $info['medium'] = round(($medium / $info['count']) * 100); //中评
            }
            $info['bad'] = 0;
            if ($bad) {
                $info['bad'] = round(($bad / $info['count']) * 100); //差评
            }
        } else {
            $info['favorable'] = 100;
            $info['medium'] = 100;
            $info['bad'] = 100;
        }
        return $info;
		
	}
    
	/**
     * 获取众筹项目的评轮列表
     */
	function crowd_get_comment($goods_id,$pay = 1, $num = 10, $start = 0){

		 if(empty($goods_id)){
            return false;
        }
      
        if(!empty($limit)){
            $limit = " LIMIT $limit";
        }
        $sql = "SELECT id,user_id,user_name,goods_id,content,add_time FROM ". $this->pre ."crowd_comment WHERE ".
               " goods_id = '".$goods_id."' AND parent_id = 0 AND status = 1 ORDER BY id DESC LIMIT $start , $num";
        $comment_list = $this->query($sql);
        foreach($comment_list as $k => $v){
			$comment_list[$k]['add_time'] = local_date(C('time_format'), $v['add_time']);
			$comment_list[$k]['name'] =  $v['user_name'];
			$comment_list[$k]['avatar'] = '';
			$wechat_user = $this->model->table('wechat_user')->where("ect_uid=".$v['user_id'])->field('nickname,headimgurl')->find();
			if(!empty($wechat_user)){
				$comment_list[$k]['name'] =  $wechat_user['nickname'];
				$comment_list[$k]['avatar'] = $wechat_user['headimgurl'];
			}
			$comment_list[$k]['reply'] =  $this->reply_content($v['id']);
			
        }
        return $comment_list;
		
	}
	
	/**
     * 获取众筹项目的支持人
     */
	function buy_num($goods_id){
		
		return $buy_num_list;
	}
	/**
     * 获取众筹项目的评轮回复信息
     */
	function reply_content($parent_id){
		$sql = "SELECT id,user_id,user_name,goods_id,content,add_time FROM ". $this->pre ."crowd_comment WHERE ".
               " parent_id = '".$parent_id."' AND status = 1 $where ORDER BY id DESC";
        $replyt_list = $this->query($sql);
        foreach($replyt_list as $k => $v){
			$replyt_list[$k]['add_time'] = local_date(C('time_format'), $v['add_time']);
        }
		 return $replyt_list;
	}
	
	/**
     * 获取众筹项目动态
     */
	function crowd_trends($goods_id){
		$sql = "SELECT id,goods_id,content,add_time FROM ". $this->pre ."crowd_trends WHERE ".
               " goods_id = '".$goods_id."' AND status = 1 $where ORDER BY id DESC ";
        $trends_list = $this->query($sql);
        foreach($trends_list as $k => $v){
			$trends_list[$k]['add_time'] = local_date(C('time_format'), $v['add_time']);
        }
        return $trends_list;
		
	}
	
	/**
     * 获取众筹项的支持者
     */
	function crowd_buy($goods_id){
		$sql = "SELECT o.user_id, o.add_time, g.goods_price  FROM ". $this->pre ."order_info as o left join  ". $this->pre ."order_goods as g on o.order_id = g.order_id". " WHERE g.goods_id = '".$goods_id."' AND o.extension_code = 'crowd_buy'  ORDER BY g.rec_id DESC ";
        $buy_list = $this->query($sql);
        foreach($buy_list as $k => $v){

			 $time = gmtime() - $v['add_time'];
			 if(($time/60) < 1){
				 $buy_list[$k]['add_time'] = '刚刚';
			 }
			 if(($time/60) > 1 && ($time/60)< 60 ){
				 $buy_list[$k]['add_time'] = intval($time/60).'分钟前';
			 }
			 if(($time/60) > 60 && ($time/60)< (60*24) ){
				 $buy_list[$k]['add_time'] = intval($time/3600).'小时前';
			 }
			 if(($time/60) > (60*24)){
				 $buy_list[$k]['add_time'] = intval($time/(3600*24)).'天前';
			 }
				 
		
			//$buy_list[$k]['add_time'] = local_date(C('time_format'), $v['add_time']);		
			
			$user = $this->model->table('users')->where("user_id=".$v['user_id'])->field('user_name')->find();		
			$wechat_user = $this->model->table('wechat_user')->where("ect_uid=".$v['user_id'])->field('nickname,headimgurl')->find();
			if(!empty($wechat_user)){
				$buy_list[$k]['name'] =  $wechat_user['nickname'];
				$buy_list[$k]['avatar'] = $wechat_user['headimgurl'];
			}else{
				$buy_list[$k]['name'] =  $user['user_name'];
				$buy_list[$k]['avatar'] = '';				
			}
        }

        return $buy_list;
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
