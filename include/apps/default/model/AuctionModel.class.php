<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：ActivityModel.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTOUCH 拍卖活动模型
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class AuctionModel extends BaseModel {

    /**
     * 取得拍卖活动数量
     * @return  int
     */
    function auction_count() {
        $now = gmtime();
        $sql = "SELECT COUNT(*) as count " .
                "FROM " . $this->pre .
                "goods_activity WHERE act_type = '" . GAT_AUCTION . "' " .
                "AND start_time <= '$now' AND end_time >= '$now' AND is_finished < 2";
        $res = $this->row($sql);
        return $res['count'];
    }

    /**
     * 取得某页的拍卖活动
     * @param   int     $size   每页记录数
     * @param   int     $page   当前页
     * @return  array
     */
    function auction_list($size, $page, $sort, $order) {
        $auction_list = array();
        $auction_list['finished'] = $auction_list['finished'] = array();

        $now = gmtime();
        $start = ($page - 1) * $size;
        $sort = $sort != 'goods_id' ? 't.' . $sort : $sort;
        $sql = "SELECT a.*,t.act_banner ,t.sales_count ,t.click_num ,  IFNULL(g.goods_thumb, '') AS goods_thumb " .
                "FROM " . $this->pre . "goods_activity AS a " .
                "LEFT JOIN " . $this->pre . "goods AS g ON a.goods_id = g.goods_id " .
                "LEFT JOIN " . $this->pre . "touch_goods_activity AS t ON a.act_id = t.act_id " .
                "LEFT JOIN " . $this->pre . "touch_goods as tg ON g.goods_id = tg.goods_id " .
                "WHERE a.act_type = '" . GAT_AUCTION . "' " .
                "AND a.start_time <= '$now' AND a.end_time >= '$now' AND a.is_finished < 2 ORDER BY $sort $order LIMIT $start ,$size ";
        $res = $this->query($sql);

        foreach ($res as $row) {
            $ext_info = unserialize($row['ext_info']);
            $auction = array_merge($row, $ext_info);
            $auction['status_no'] = auction_status($auction);

            $auction['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $auction['start_time']);
            $auction['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $auction['end_time']);
            $auction['formated_start_price'] = price_format($auction['start_price']);
            $auction['formated_end_price'] = price_format($auction['end_price']);
            $auction['formated_deposit'] = price_format($auction['deposit']);
            $auction['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $auction['act_banner'] = $row['act_banner'] ? $row['act_banner'] : $auction['goods_thumb'];
            $auction['url'] = build_uri('auction', array('auid' => $auction['act_id']));

            if ($auction['status_no'] < 2) {
                $auction_list['under_way'][] = $auction;
            } else {
                $auction_list['finished'][] = $auction;
            }
        }


        $auction_list = @array_merge($auction_list['under_way'], $auction_list['finished']);
        return $auction_list;
    }

}
