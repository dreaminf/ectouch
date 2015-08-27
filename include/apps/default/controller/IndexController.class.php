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
        $drp_id = I('drp_id') ? I('drp_id') : 0;
        if($drp_id > 0){
            //分销店铺
            $drp_info = model('Sale')->get_drp($drp_id);
            if($drp_info['open'] == 1){
                $drp_info['cat_id'];
                session('drp_cat',substr($drp_info['cat_id'],0,-1));
                $_SESSION['parent_id'] = $drp_id;
                $drp_info['mobile_qr'] = './data/attached/drp/drp_'.$drp_id.'.png';
                if(!file_exists($drp_info['mobile_qr'])){
                    // 二维码
                    $url = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?drp_id='.$drp_id;
                    // 纠错级别：L、M、Q、H
                    $errorCorrectionLevel = 'L';
                    // 点的大小：1到10
                    $matrixPointSize = 8;
                    QRcode::png($url, $drp_info['mobile_qr'], $errorCorrectionLevel, $matrixPointSize, 2);
                }
                $this->assign('drp_info',$drp_info);
            }
        }
        // 自定义导航栏
        $navigator = model('Common')->get_navigator();
        $this->assign('navigator', $navigator['middle']);
        $this->assign('best_goods', model('Index')->goods_list('best', C('page_size')));
        $this->assign('new_goods', model('Index')->goods_list('new', C('page_size')));
        $this->assign('hot_goods', model('Index')->goods_list('hot', C('page_size')));
        // 调用促销商品
        $this->assign('promotion_goods', model('Index')->goods_list('promotion', C('page_size')));
        //首页推荐分类
        $cat_rec = model('Index')->get_recommend_res();
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
        $this->display('index.dwt');
    }

    /**
     * ajax获取商品
     */
    public function ajax_goods() {
        if (IS_AJAX) {
            $type = I('get.type');
            $start = $_POST['last'];
            $limit = $_POST['amount'];
            $hot_goods = model('Index')->goods_list($type, $limit, $start);
            $list = array();
            // 热卖商品
            if ($hot_goods) {
                foreach ($hot_goods as $key => $value) {
                    $this->assign('hot_goods', $value);
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

}
