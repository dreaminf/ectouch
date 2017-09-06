<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：GoodsControoller.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：商品详情控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class GoodsController extends CommonController
{

    protected $goods_id;

    /**
     * 构造函数   加载user.php的语言包 并映射到模版
     */
    public function __construct()
    {
        parent::__construct();
        $this->goods_id = isset($_REQUEST ['id']) ? intval($_REQUEST ['id']) : 0;
        $this->team_id = isset($_REQUEST ['team_id']) ? intval($_REQUEST ['team_id']) : 0;

    }

    /**
     *  商品详情页
     */
    public function index()
    {
        // 获得商品的信息
        $goods = model('Goods')->get_goods_info($this->goods_id);
        //购物车商品数量
        $cart_goods = insert_cart_info_number();
        $this->assign('seller_cart_total_number', $cart_goods);
		//获取qq客户号码
		$shop_qq = $this->model->table('shop_config')->field('value')->where(array("code"=>qq))->getOne();
		if($shop_qq){
			$infoqq['centent'] = explode(',',$shop_qq);
		}
		$this->assign('shop_qq', $infoqq);
        // 如果没有找到任何记录则跳回到首页
        if ($goods === false) {
            ecs_header("Location: ./\n");
        } else {
            if ($goods ['brand_id'] > 0) {
                $goods ['goods_brand_url'] = url('brand/index', array('id' => $goods ['brand_id']));
            }
            $shop_price = $goods ['shop_price'];
            $linked_goods = model('Goods')->get_related_goods($this->goods_id);
            $goods ['goods_style_name'] = add_style($goods ['goods_name'], $goods ['goods_name_style']);

            // 购买该商品可以得到多少钱的红包
            if ($goods ['bonus_type_id'] > 0) {
                $time = gmtime();
                $condition = "type_id = '$goods[bonus_type_id]' " . " AND send_type = '" . SEND_BY_GOODS . "' " . " AND send_start_date <= '$time'" . " AND send_end_date >= '$time'";
                $count = $this->model->table('bonus_type')->field('type_money')->where($condition)->getOne();

                $goods ['bonus_money'] = floatval($count);
                if ($goods ['bonus_money'] > 0) {
                    $goods ['bonus_money'] = price_format($goods ['bonus_money']);
                }
            }
            $comments = model('Comment')->get_comment_info($this->goods_id, 0);
            $this->assign('goods', $goods);
            $this->assign('comments', $comments);
            $this->assign('goods_id', $goods ['goods_id']);
            $this->assign('promote_end_time', $goods ['gmt_end_time']);
            // 获得商品的规格和属性
            $properties = model('Goods')->get_goods_properties($this->goods_id);
            // 商品属性
            $this->assign('properties', $properties ['pro']);
            // 商品规格
            $this->assign('specification', $properties ['spe']);
            // 相同属性的关联商品
            $this->assign('attribute_linked', model('Goods')->get_same_attribute_goods($properties));
            // 关联商品
            $this->assign('related_goods', $linked_goods);
            // 关联文章
            $this->assign('goods_article_list', model('Goods')->get_linked_articles($this->goods_id));
            // 配件
            $this->assign('fittings', model('Goods')->get_goods_fittings(array($this->goods_id)));
            // 会员等级价格
            $this->assign('rank_prices', model('Goods')->get_user_rank_prices($this->goods_id, $shop_price));
            // 商品相册
            $this->assign('pictures', model('GoodsBase')->get_goods_gallery($this->goods_id));
            // 获取关联礼包
            $package_goods_list = model('Goods')->get_package_goods_list($goods ['goods_id']);
            $this->assign('package_goods_list', $package_goods_list);
            //取得商品优惠价格列表
            $volume_price_list = model('GoodsBase')->get_volume_price_list($goods ['goods_id'], '1');
            // 商品优惠价格区间
            $this->assign('volume_price_list', $volume_price_list);
        }

        // 检查是否已经存在于用户的收藏夹
        if ($_SESSION ['user_id']) {
            $where['user_id'] = $_SESSION ['user_id'];
            $where['goods_id'] = $this->goods_id;
            $rs = $this->model->table('collect_goods')->where($where)->count();
            if ($rs > 0) {
                $this->assign('sc', 1);
            }
        }

        /* 记录浏览历史 */
        if (!empty($_COOKIE ['ECS'] ['history'])) {
            $history = explode(',', $_COOKIE ['ECS'] ['history']);
            array_unshift($history, $this->goods_id);
            $history = array_unique($history);
            while (count($history) > C('history_number')) {
                array_pop($history);
            }
            setcookie('ECS[history]', implode(',', $history), gmtime() + 3600 * 24 * 30);
        } else {
            setcookie('ECS[history]', $this->goods_id, gmtime() + 3600 * 24 * 30);
        }
        $comment_list = model('Comment')->get_comment($this->goods_id, 1,0,4,0);
        $this->assign('comment_list', $comment_list);
        // 更新点击次数
        $data = 'click_count = click_count + 1';
        $this->model->table('goods')->data($data)->where('goods_id = ' . $this->goods_id)->update();

        // 当前系统时间
        $this->assign('now_time', gmtime());
        $this->assign('sales_count', model('GoodsBase')->get_sales_count($this->goods_id));
        $this->assign('image_width', C('image_width'));
        $this->assign('image_height', C('image_height'));
        $this->assign('id', $this->goods_id);
        $this->assign('type', 0);
        $this->assign('cfg', C('CFG'));
        // 促销信息
        $this->assign('promotion', model('GoodsBase')->get_promotion_info($this->goods_id));
        $this->assign('title', L('goods_detail'));
        /* 页面标题 */
        $page_info = get_page_title($goods['cat_id'], $goods['goods_name']);
        /* meta */
        $this->assign('meta_keywords', htmlspecialchars($goods['keywords']));
        $this->assign('meta_description', htmlspecialchars($goods['goods_brief']));
        $this->assign('ur_here', $page_info['ur_here']);
        $this->assign('page_title', $page_info['title']);
        // 微信JSSDK分享
        $share_data = array(
            'title' => $goods['goods_name'],
            'desc' => $goods['goods_brief'],
            'link' => '',
            'img' => $goods['goods_img'],
        );
        $this->assign('share_data', $this->get_wechat_share_content($share_data));
        //组合套餐名 start
        $comboTabIndex = array(' ','一', '二', '三','四','五','六','七','八','九','十');
        $this->assign('comboTab',$comboTabIndex);
        //组合套餐组
        $fittings_list = model('Goods')->get_goods_fittings(array($this->goods_id));
        $fittings_index = array();
        if(is_array($fittings_list)){
            foreach($fittings_list as $vo){
                $fittings_index[$vo['group_id']] = 1;//关联数组
            }
        }
        ksort($fittings_index);//重新排序
        $this->assign('fittings_tab_index', $fittings_index);//套餐数量
        // 组合套餐名 end
        //获取该商品已成功开团信息
        $team_log = model('Goods')->team_goods_log($this->goods_id);
        $this->assign('team_log', $team_log);
        $this->assign('team_id', $this->team_id);

        $this->display('team/goods.html');

    }

    /**
     * 商品信息
     */
    public function info()
    {
        /* 获得商品的信息 */
        $goods = model('Goods')->get_goods_info($this->goods_id);
		//购物车商品数量
        $cart_goods = insert_cart_info_number();
        $this->assign('seller_cart_total_number', $cart_goods);
        /* 页面标题 */
        $page_info = get_page_title($goods['cat_id'], $goods['goods_name']);
        $this->assign('page_title', htmlspecialchars($page_info['title']));
        /* meta */
        $this->assign('meta_keywords', htmlspecialchars($goods['keywords']));
        $this->assign('meta_description', htmlspecialchars($goods['goods_brief']));

        $this->assign('goods', $goods);
        $properties = model('Goods')->get_goods_properties($this->goods_id);  // 获得商品的规格和属性
        $this->assign('properties', $properties['pro']);                      // 商品属性
        $this->assign('specification', $properties['spe']);                   // 商品规格
        $this->assign('id', $this->goods_id);
        $this->assign('title', L('detail_intro'));
        $this->display('goods_info.dwt');

    }

    /**
     * 商品评论
     */
    public function comment_list()
    {
        if(empty($_GET['type']) && !empty($_SESSION['type'])){
            $_SESSION['type'] = $_SESSION['type'];
        }elseif(!empty($_SESSION['type']) && $_SESSION['type'] != I('request.type')){
            $this->type = I('request.type') ? intval(I('request.type')) : 1 ;
            $_SESSION['type'] = $this->type;
        }else{
            $this->type = I('request.type') ? intval(I('request.type')) : 1 ;
            $_SESSION['type'] = $this->type;
        }
        $cmt = new stdClass();
        if(empty($_GET['id']) && !empty($_SESSION['cmt_id'])){
            $_SESSION['cmt_id'] = $_SESSION['cmt_id'];
        }elseif(!empty($_SESSION['cmt_id']) && $_SESSION['cmt_id'] != I('request.id')){
            $cmt->id = I('request.id') ? intval(I('request.id')) : 1 ;
            $_SESSION['cmt_id'] = $cmt->id;
        }else{
            $cmt->id = I('request.id') ? intval(I('request.id')) : 0 ;
            $_SESSION['cmt_id'] = $cmt->id;
        }
        $com = model('Comment')->get_comment_info($_SESSION['cmt_id'],0);
        $this->assign('comments_info', $com);
        $pay = 0;
        $size = I(C('page_size'), 10);
        $this->assign('show_asynclist', C('show_asynclist'));
        $count = $com['count'];
        $filter['page'] = '{page}';
        $offset = $this->pageLimit(url('goods/comment_list', $filter), $size);
        $offset_page = explode(',', $offset);
        $comment_list = model('Comment')->get_comment($_SESSION['cmt_id'], $_SESSION['type'], $pay, $offset_page[1], $offset_page[0]);
        $this->assign('comment_list', $comment_list);
        $this->assign('username', $_SESSION['user_name']);
        $this->assign('email', $_SESSION['email']);
        /* 验证码相关设置 */
        if ((intval(C('captcha')) & CAPTCHA_COMMENT) && gd_version() > 0) {
            $this->assign('enabled_captcha', 1);
            $this->assign('rand', mt_rand());
        }
        $result['message'] = C('comment_check') ? L('cmt_submit_wait') : L('cmt_submit_done');
        $this->assign('id', $_SESSION['cmt_id']);
        $this->assign('type', $_SESSION['type']);
        $this->assign('pager', $this->pageShow($count));
        $this->assign('title', L('goods_comment'));
        $this->display('team/goods_comment_list.html');
    }


    /**
     * 改变属性、数量时重新计算商品价格
     */
    public function price()
    {
        //格式化返回数组
        $res = array(
            'err_msg' => '',
            'result' => '',
            'qty' => 1
        );
        // 获取参数
        $attr_id = isset($_REQUEST ['attr']) ? explode(',', $_REQUEST ['attr']) : array();
        $goods_attr_id = isset($_REQUEST ['attr']) ? intval($_REQUEST ['attr']) : 0;
        $number = (isset($_REQUEST ['number'])) ? intval($_REQUEST ['number']) : 1;

        // 如果商品id错误
        if ($this->goods_id == 0) {
            $res ['err_msg'] = L('err_change_attr');
            $res ['err_no'] = 1;
        } else {
            // 查询
            $condition = 'goods_id =' . $this->goods_id;
            $goods = $this->model->table('goods')->field('goods_name , goods_number ,extension_code')->where($condition)->find();
            $attr_id = count($attr_id) > 1 ? str_replace(',', '|', $_REQUEST ['attr']) : $attr_id['0'];
            $condition = 'goods_attr = '."'".$attr_id."'";
            $product = $this->model->table('products')->field('product_number')->where($condition)->find();

            if ($number <= 0) {
                $res ['qty'] = $number = 1;
            } else {
                $res ['qty'] = $number;
            }
            $shop_price = model('GoodsBase')->get_final_price($this->goods_id, $number, true, $attr_id);
            $res ['result'] = price_format($shop_price * $number);
            if(!empty($product['product_number'])) {
                $res ['product_number'] = '库存：'.$product['product_number'];
            }
        }
        die(json_encode($res));
    }


    /**
    * 改变属性、数量时重新计算拼团商品价格
    */
    public function team_price()
    {
        //格式化返回数组
        $res = array(
            'err_msg' => '',
            'result' => '',
            'qty' => 1
        );
        // 获取参数
        $attr_id = isset($_REQUEST ['attr']) ? explode(',', $_REQUEST ['attr']) : array();
        $number = (isset($_REQUEST ['number'])) ? intval($_REQUEST ['number']) : 1;
        // 如果商品id错误
        if ($this->goods_id == 0) {
            $res ['err_msg'] = L('err_change_attr');
            $res ['err_no'] = 1;
        } else {
            // 查询
            $condition = 'goods_id =' . $this->goods_id;
            $goods = $this->model->table('goods')->field('goods_name , goods_number ,extension_code,astrict_num')->where($condition)->find();

            if ($number <= 0) {
                $res ['qty'] = $number = 1;
            } else {
                $res ['qty'] = $number;
            }
            //验证拼团限购数量
            if($number > $goods ['astrict_num'] ){
                $res ['err_msg'] = '已超过拼团限购数量';
                $res ['err_no'] = 1;
                $res ['qty'] = $goods ['astrict_num'];
            }


            $shop_price = model('GoodsBase')->team_get_final_price($this->goods_id, $number, true, $attr_id);
            //$res ['result'] = price_format($shop_price * $number);
            $res ['result'] = '￥'.$shop_price * $number.'元';
        }
        die(json_encode($res));
    }
    /* ------------------------------------------------------ */

    //--拼团商品 --> 购买
    /* ------------------------------------------------------ */
    public function team_buy() {

        /* 查询：判断是否登录 */
        if ($_SESSION['user_id'] <= 0) {
            $this->redirect(url('user/login'));
        }

        /* 查询：取得数量 */
        $goods_id = isset($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;
        $team_id = isset($_POST['team_id']) ? intval($_POST['team_id']) : 0;

        if ($goods_id <= 0) {
            ecs_header("Location: ./\n");
            exit;
        }
        $number = isset($_POST['number']) ? intval($_POST['number']) : 1;
        $number = $number < 1 ? 1 : $number;

        // 查询：系统启用了库存，检查输入的商品数量是否有效
        // 查询

        $arrGoods = $this->model->table('goods')->field('goods_sn,goods_name,goods_number,extension_code,team_price')->where('goods_id =' . $goods_id)->find();
        $goodsnmber = model('Users')->get_goods_number($goods_id);
        $goodsnmber+=$number;
        if (intval(C('use_storage')) > 0 && $arrGoods ['extension_code'] != 'package_buy') {
            if ($arrGoods ['goods_number'] < $goodsnmber) {
               show_message(sprintf(L('stock_insufficiency'), $arrGoods ['goods_name'], $arrGoods ['goods_number'], $arrGoods ['goods_number']), '', '', 'error');
            }
        }
        $goods = $this->model->table('goods')->where('goods_id =' . $goods_id)->find();
        // 检查：商品数量是否合法
        if (!is_numeric($number) || intval($number) <= 0) {
             show_message(L('invalid_number'), '', '', 'error');
        }

        //验证拼团限购数量
        if($number > $goods['astrict_num'] ){
             show_message('已超过拼团限购数量', '', '', 'error');
        }
        /* 查询：取得规格 */
        $specs = '';
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'spec_') !== false) {
                $specs .= ',' . intval($value);
            }
        }
        $specs = trim($specs, ',');
        /* 查询：如果商品有规格则取规格商品信息 配件除外 */
        if ($specs) {
            $_specs = explode(',', $specs);
            $product_info = model('ProductsBase')->get_products_info($goods_id, $_specs);
        }
        empty($product_info) ? $product_info = array('product_number' => 0, 'product_id' => 0) : '';

        /* 查询：判断指定规格的货品数量是否足够 */
        if ($specs && $number > $product_info['product_number']) {
            show_message(L('gb_error_goods_lacking'), '', '', 'error');
        }

        /* 查询：查询规格名称和值，不考虑价格 */
        $attr_list = array();
        $sql = "SELECT a.attr_name, g.attr_value " .
                "FROM " . $this->model->pre . "goods_attr AS g, " .
                $this->model->pre . "attribute AS a " .
                "WHERE g.attr_id = a.attr_id " .
                "AND g.goods_attr_id " . db_create_in($specs);
        $res = $this->model->query($sql);
        foreach ($res as $row) {
            $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
        }
        $goods_attr = join(chr(13) . chr(10), $attr_list);

        /* 更新：清空购物车中所有团购商品 */
        model('Order')->clear_cart(CART_TEAM_GOODS);

        /* 更新：加入购物车 */
        $goods_price = $goods ['team_price'];
        //$goods_price = model('GoodsBase')->get_final_price($goods_id, $number, true, $spec);
        $cart = array(
            'user_id' => $_SESSION['user_id'],
            'session_id' => SESS_ID,
            'goods_id' => $goods_id,
            'product_id' => $product_info['product_id'],
            'goods_sn' => addslashes($goods['goods_sn']),
            'goods_name' => addslashes($goods['goods_name']),
            'market_price' => $goods['market_price'],
            'goods_price' => $goods_price,
            'goods_number' => $number,
            'goods_attr' => addslashes($goods_attr),
            'goods_attr_id' => $specs,
            'is_real' => $goods['is_real'],
            'is_shipping' => $goods['is_shipping'],
            'extension_code' => addslashes($goods['extension_code']),
            'parent_id' => 0,
            'rec_type' => CART_TEAM_GOODS,
            'is_gift' => 0,
            'is_selected' => 1
        );
        $new_cart = model('Common')->filter_field('cart', $cart);
        $this->model->table('cart')->data($new_cart)->insert();

        /* 更新：记录购物流程类型：拼团 */
        $_SESSION['flow_type'] = CART_TEAM_GOODS;
        $_SESSION['extension_code'] = 'team_buy';
        $_SESSION['team_id'] = $team_id;

        /* 进入收货人页面 */
        $this->redirect(url('flow/checkout'));
        exit;
    }

    /* ------------------------------------------------------ */
    //--拼团商品 --> 等待成团
    /* ------------------------------------------------------ */
    public function team_goods_wait() {
        $team_id = isset($_REQUEST ['team_id']) ? intval($_REQUEST ['team_id']) : 0;
        if ($team_id <= 0) {
            ecs_header("Location: ./\n");
            exit;
        }

        /* --获取拼团商品信息-- */
        $sql ="select tl.team_id, tl.start_time,o.team_parent_id,g.goods_id,g.goods_img,g.validity_time ,g.goods_name,g.team_num,g.team_price,og.goods_attr from " . $this->model->pre . "team_log as tl LEFT JOIN " . $this->model->pre. "order_info as o ON tl.team_id = o.team_id LEFT JOIN  ". $this->model->pre. "order_goods as og on o.order_id = og.order_id LEFT JOIN " . $this->model->pre ."goods as g ON tl.goods_id = g.goods_id where tl.team_id =$team_id  and o.pay_status = '" . PS_PAYED . "' and o.extension_code ='team_buy' and o.team_parent_id > 0";
        $result = $this->model->query($sql);

        foreach ($result as $vo) {
            $goods['goods_id'] = $vo['goods_id'];
            $goods['goods_name'] = $vo['goods_name'];
            $goods['goods_img'] = get_image_path($goods_id, $vo['goods_img']);
            $goods['team_id'] = $vo['team_id'];//开团id
            $goods['goods_attr'] = $vo['goods_attr'];
            $goods['team_num'] = $vo['team_num'];
            $goods['team_price'] = $vo['team_price'];
            $user = $this->model->table('users')->where("user_id=" . $vo['team_parent_id'])->field('user_name')->find();
            $goods['name'] = $user['user_name'];
            $goods['avatar'] = '';
            $wechat_user = $this->model->table('wechat_user')->where("ect_uid=" . $vo['team_parent_id'])->field('nickname,headimgurl')->find();
            if (!empty($wechat_user)) {
                $goods['name'] = $wechat_user['nickname'];
                $goods['avatar'] = $wechat_user['headimgurl'];
            }
        }
        /* --获取拼团信息-- */
        $sql ="select tl.team_id, tl.start_time,tl.goods_id,tl.status,g.validity_time ,g.team_num,g.team_price from " . $this->model->pre . "team_log as tl LEFT JOIN " . $this->model->pre ."goods as g ON tl.goods_id = g.goods_id where tl.team_id =$team_id  ";
        $team = $this->model->getRow($sql);
        //dump($team);
        $team['end_time'] = $team['start_time']+($team['validity_time']*3600);//剩余时间
        $surplus = model('Goods')->surplus_num($team['team_id']);//几人参团
        $team['surplus'] =$team['team_num']-$surplus;//还差几人
        $team['bar'] = round($surplus*100/$team['team_num'],0);//百分比
        if($team['status'] != 1 &&gmtime()< ($team['start_time']+($team['validity_time']*3600))){//进项中
            $team['status'] = 0;
            $result = '等待成团';
        }elseif($team['status'] != 1 && gmtime()> ($team['start_time']+($team['validity_time']*3600))){//失败
            $team['status'] = 2;
            $result = '拼团失败';
        }elseif($team['status'] = 1){//成功
            $team['status'] = 1;
            $result = '拼团成功';
        }

        /* --获取拼团团员信息-- */
        $sql ="select o.team_id, o.user_id,o.team_parent_id,o.team_user_id from " . $this->model->pre . "order_info as o LEFT JOIN " . $this->model->pre ."users as u ON o.user_id = u.user_id where o.team_id =$team_id and o.extension_code ='team_buy' and o.pay_status = '" . PS_PAYED ."' order by o.add_time asc limit 0,5";
        $team_user = $this->model->query($sql);
        foreach ($team_user as $key => $vo) {
            $team_user[$key]['avatar'] = '';
            $wechat_user = $this->model->table('wechat_user')->where("ect_uid=" . $vo['user_id'])->field('nickname,headimgurl')->find();
            if (!empty($wechat_user)) {
                $team_user[$key]['name'] = $wechat_user['nickname'];
                $team_user[$key]['avatar'] = $wechat_user['headimgurl'];
            }
        }

        /* --验证是否已经参团-- */
        $team_join = $this->model->table('order_info')->where(array('user_id'=>$_SESSION['user_id'],'team_id'=>$team_id))->count();
        if ($team_join > 0) {
           $this->assign('team_join', 1);
        }

        // 微信JSSDK分享
        $share_data = array(
            'title' => $result,
            'desc' => $goods['name'].'邀请您一起拼团',
            'link' => '',
            'img' => $goods['goods_img'],
        );
        $this->assign('share_data', $this->get_wechat_share_content($share_data));

        $this->assign('team_user', $team_user);
        $this->assign('goods', $goods);
        $this->assign('team', $team);
        $this->assign('cfg', C(cfg));
        $this->assign('title', $result);
        $this->display('team/goods-wait.html');
    }

    /* ------------------------------------------------------ */
    //--拼团商品 --> 品团成员
    /* ------------------------------------------------------ */
    public function goods_wait_list() {

        $team_id = isset($_REQUEST ['team_id']) ? intval($_REQUEST ['team_id']) : 0;
        $sql ="select o.team_id, o.user_id,o.team_parent_id,o.team_user_id,o.add_time ,u.user_name from " . $this->model->pre . "order_info as o LEFT JOIN " . $this->model->pre ."users as u ON o.user_id = u.user_id where o.team_id =$team_id and o.extension_code ='team_buy' and o.pay_status = '" . PS_PAYED ."' order by o.add_time asc ";
        $team_user = $this->model->query($sql);
        foreach ($team_user as $key => $vo) {
            $team_user[$key]['add_time'] = local_date(C('time_format'), $vo['add_time']);
            $team_user[$key]['name'] = $vo['user_name'];
            $team_user[$key]['avatar'] = '';
            $wechat_user = $this->model->table('wechat_user')->where("ect_uid=" . $vo['user_id'])->field('nickname,headimgurl')->find();
            if (!empty($wechat_user)) {
                $team_user[$key]['name'] = $wechat_user['nickname'];
                $team_user[$key]['avatar'] = $wechat_user['headimgurl'];
            }
        }
        $this->assign('team_user', $team_user);
        $this->display('team/goods-wait-list.html');
    }

    /**
     * ajax获取等待成团热卖商品
     */
    public function ajax_goods() {
        if (IS_AJAX) {
            $type = I('get.type');
            $id = I('get.id');
            $start = $_POST['last'];
            $limit = $_POST['amount'];
            $goods_list = model('Index')->goods_list($type,$id, $limit, $start);
            $list = array();
            // 热卖商品
            if ($goods_list) {
                foreach ($goods_list as $key => $value) {
                    $value['iteration'] = $key + 1;
                    $this->assign('team_goods', $value);
                    $list [] = array(
                        'single_item' => ECTouch::view()->fetch('library/asynclist_info.lbi')
                    );
                }
            }
            echo json_encode($list);
            exit();
        } else {
            $this->redirect(url('goods/team_goods_wait'));
        }
    }


}