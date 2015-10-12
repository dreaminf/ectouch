<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：SaleModel.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTouch 用户模型
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class SaleModel extends BaseModel {
    /**
     * 获取店铺列表
     */
    function get_shop_list($key=1){
        $res = array();
        $sql = "select d.* from {pre}users as u JOIN {pre}drp_shop d ON  u.user_id=d.user_id WHERE u.parent_id = ".$_SESSION['user_id'] ." and apply_sale = 1";
        $list = M()->query($sql);
        if($key == 1){
            $res = $list;
        }else{
            if($list){
                $where = '';
                foreach ($list as $k => $val){
                    $where .= $val['user_id'].',';
                }
                $where = substr($where, 0, -1);
                $sql = "select d.* from {pre}users  as u JOIN {pre}drp_shop d ON  u.user_id=d.user_id WHERE u.parent_id in($where) and apply_sale = 1";
                $list2 = M()->query($sql);
                if($key == 2){
                    $res = $list2;
                }else{
                    if($list2){
                        $where = '';
                        foreach ($list2 as $k2 => $val){
                            $where .= $val['user_id'].',';
                        }
                        $where = substr($where, 0, -1);
                        $sql = "select d.* from {pre}users as u JOIN {pre}drp_shop d ON  u.user_id=d.user_id WHERE u.parent_id in($where) and apply_sale = 1";
                        $list3 = M()->query($sql);
                        if($key == 3){
                            $res = $list3;
                        }else{
                            return false;
                        }
                    }else{
                        return false;
                    }
                }
            }else{
                return false;
            }
        }
        foreach($res as $key => $val){
            $res[$key]['time'] = local_date('Y-m-d H:i:s',$val['create_time']);
        }
        return $res;
    }

    /**
     * 获取我的下线会员
     */
    function get_user_list($key=1){
        $res = array();
        $sql = "select * from {pre}users  WHERE parent_id = ".$_SESSION['user_id'];
        $list = M()->query($sql);
        if($key == 1){
            $res = $list;
        }else{
            if($list){
                $where = '';
                foreach ($list as $k => $val){
                    $where.=','.$val['user_id'];
                }

                $sql = "select * from {pre}users WHERE parent_id in($where) ";
                $list2 = M()->query($sql);
                if($key == 2){
                    $res = $list2;
                }else{
                    if($list2){
                        $where = '';
                        foreach ($list2 as $k2 => $val){
                            $where.=','.$val['user_id'];
                        }
                        $sql = "select * from {pre}users WHERE parent_id in($where)";
                        $list3 = M()->query($sql);
                        if($key == 3){
                            $res = $list3;
                        }else{
                            return false;
                        }
                    }else{
                        return false;
                    }
                }
            }else{
                return false;
            }
        }
        foreach($res as $key => $val){
            $res[$key]['time'] = local_date('Y-m-d',$val['reg_time']);

            if(class_exists('WechatController')){
                if (method_exists('WechatController', 'get_avatar')) {
                    $u_row = call_user_func(array('WechatController', 'get_avatar'), $val['user_id']);
                }
            }
            if ($u_row) {
                $res[$key]['username'] = $u_row['nickname'];
            }
        }
        return $res;
    }

    /**
     * 查询销售总额
     */
    function get_sale_money_total($uid=0){
        $uid = $uid > 0 ? $uid : $_SESSION['user_id'];
        $drp_id = $this->model->table('drp_shop')->where("user_id=".$uid)->field('id')->getOne();
        $goods_info =  M()->getRow("select sum(goods_amount) as money from {pre}order_info where pay_status='".PS_PAYED."' and drp_id = ".$drp_id);
        return $goods_info['money'];
    }
    /**
     * 查询分销商佣金
     * @access
     * @param   int     $user_id        会员ID
     * @return  int
     */
    function saleMoney($uid=0) {
        $uid = $uid > 0 ? $uid : $_SESSION['user_id'];
        $money = M()->getRow("select sum(user_money) as money from {pre}drp_log where user_id = ".$uid ." and user_money > 0");
        $money = $money['money'];
        return $money ? $money : 0;

    }

    /**
     * 查询分销商当日佣金
     * @access
     * @param   int     $user_id        会员ID
     * @return  int
     */
    function saleMoney_today($uid=0) {
        $uid = $uid > 0 ? $uid : $_SESSION['user_id'];
        $user_money =  M()->getRow("select sum(user_money) as money from {pre}drp_log where user_id = ".$uid ." and change_time > ".strtotime(local_date('Y-m-d')));
        return $user_money['money'];

    }

    /**
     * 查询分销商可提现佣金
     * @access
     * @param   int     $user_id        会员ID
     * @return  int
     */
    function saleMoney_surplus($uid=0) {
        $uid = $uid > 0 ? $uid : $_SESSION['user_id'];
        $money = M()->getRow("select sum(user_money) as money from {pre}drp_log where user_id = ".$uid);
        $money = $money['money'];
        return $money ? $money : 0;

    }

    /**
     * 查询会员账户明细
     * @access
     * @param   int     $user_id    会员ID
     * @param   int     $num        每页显示数量
     * @param   int     $start      开始显示的条数
     * @return  array
     */
    function get_sale_log($user_id, $num, $start) {
        // 获取余额记录
        $account_log = array();

        $sql = "SELECT * FROM  {pre}drp_log WHERE user_id = " . $user_id . ' AND user_money <> 0 ' .
            " ORDER BY log_id DESC limit " . $start . ',' . $num;
        $res = M()->query($sql);

        if (empty($res)) {
            return array();
            exit;
        }

        foreach ($res as $k => $v) {
            $res[$k]['change_time'] = local_date(C('date_format'), $v['change_time']);
            $res[$k]['type'] = $v['user_money'] > 0 ? L('account_inc') : L('account_dec');
            $res[$k]['user_money'] = price_format(abs($v['user_money']), false);
            $res[$k]['frozen_money'] = price_format(abs($v['frozen_money']), false);
            $res[$k]['rank_points'] = abs($v['rank_points']);
            $res[$k]['pay_points'] = abs($v['pay_points']);
            $res[$k]['short_change_desc'] = sub_str($v['change_desc'], 60);
            $res[$k]['amount'] = $v['user_money'];
        }

        return $res;


    }




    /**
     * 获取分销商id
     * @param int $parent_id
     */
    function get_parent_id($parent_id){
        $parent_id_ =  M()->table('users')->field('parent_id')->where('user_id = '.session('user_id'))->find();
        return $parent_id_['parent_id'] > 0 ? $parent_id_['parent_id'] : $parent_id;

    }

    /**
     *  获取用户的分销订单列表
     *
     * @access
     * @param   int         $user_id        用户ID号
     * @param   int         $pay            订单状态，0未付款，1全部，默认1
     * @param   int         $num            列表最大数量
     * @param   int         $start          列表起始位置
     * @return  array       $order_list     订单列表
     */
    function get_sale_orders($where, $num = 10, $start = 0 ,$user_id) {
        /* 取得订单列表 */
        $arr = array();
        $sql = "SELECT order_id, order_sn, user_id, shipping_id, order_status, shipping_status, pay_status, add_time, is_separate,drp_id,shop_separate, " .
            "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee " .
            " FROM {pre}order_info " .
            " WHERE  " . $where . " ORDER BY add_time DESC LIMIT $start , $num";
        $res = M()->query($sql);
        foreach ($res as $key => $value) {

            $value['shipping_status'] = ($value['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $value['shipping_status'];
            $value['order_status'] = L('os.' . $value['order_status']) . ',' . L('ps.' . $value['pay_status']) . ',' . L('ss.' . $value['shipping_status']);
            $goods_list = model('Order')->order_goods($value['order_id']);
            foreach ($goods_list as $key => $val) {
                $goods_list[$key]['price'] = $val['goods_price'];
                $goods_list[$key]['goods_price'] = price_format($val['goods_price'], false);
                $goods_list[$key]['subtotal'] = price_format($val['subtotal'], false);
                $goods_list[$key]['goods_thumb'] = get_image_path($value['order_id'], $val['goods_thumb']);
                $goods_list[$key]['goods_number'] = $val['goods_number'];
                $goods_list[$key]['touch_fencheng'] = $val['touch_fencheng'];
                $goods_list[$key]['touch_sale'] = $val['touch_sale'];
            }
            $arr[] = array('order_id' => $value['order_id'],
                'user_name' => M()->table('users')->field('user_name')->where("user_id=".$value[user_id])->getOne(),
                'order_sn' => $value['order_sn'],
                'img' => get_image_path(0, model('Order')->get_order_thumb($value['order_id'])),
                'order_time' => local_date(C('time_format'), $value['add_time']),
                'order_status' => $value['order_status'],
                'shipping_id' => $value['shipping_id'],
                'total_fee' => price_format($value['total_fee'], false),
                'url' => url('user/order_detail', array('order_id' => $value['order_id'])),
                'is_separate' => $value['shop_separate'] > 0 ? "<span style='font-weight:bold'>已分成</span>" : "<span style='color:red;font-weight:bold'>未分成</span>",
                'goods'=>$goods_list,
            );
        }
        return $arr;
    }

    /**
     *  获取我的会员数量
     * @param $key
     */
    function get_user_count($user_id = 0){
        $global = getInstance();
        $user_id = $user_id > 0 ? $user_id : $_SESSION['user_id'];
        $list = array(); // 用户一级下线
        $list2 = array(); // 用户二级下线
        $list3 = array(); // 用户三级下线
        // 获取用户一级下线
        $sql = "select user_id,parent_id from {pre}users where parent_id=".$user_id;
        $list = M()->query($sql);
        // 获取用户二级下线
        if($list){
            $where = '';
            foreach ($list as $key=>$val){
                $where.=','.$val['user_id'];
            }
            $sql = "select user_id,parent_id from {pre}users where parent_id in ($where)";
            $list2 = M()->query($sql);
            // 获取用户三级下线
            if($list2){
                $where = '';
                foreach ($list2 as $key=>$val){
                    $where.=','.$val['user_id'];
                }
                $sql = "select user_id,parent_id from {pre}users where parent_id in ($where)";
                $list3 = M()->query($sql);
            }
        }
        $info['count'] = count($list)+count($list2)+count($list3);
        $info['list'] = $list;
        $info['list2'] = $list2;
        $info['list3'] = $list3;
        return $info;
    }

    /**
     *  获取用户的分销订单列表
     *
     * @access
     * @param   int         $user_id        用户ID号
     * @param   int         $pay            订单状态，0未付款，1全部，默认1
     * @param   int         $num            列表最大数量
     * @param   int         $start          列表起始位置
     * @return  array       $order_list     订单列表
     */
    function get_grade_order($where) {
        $global = getInstance();
        /* 取得订单列表 */
        $arr = array();
        $sql = "SELECT order_id  FROM {pre}order_info WHERE  " . $where;
        $res = M()->query($sql);
        foreach ($res as $key => $value) {
            $arr[] = array('order_id' => $value['order_id']);
        }
        return $arr;
    }

    /**
     *  获取分销指订单的详情
     *
     * @access
     * @param   int         $order_id       订单ID
     * @param   int         $user_id        用户ID
     *
     * @return   arr        $order          订单所有信息的数组
     */
    function get_order_detail($order_id, $user_id = 0) {

        $order_id = intval($order_id);
        if ($order_id <= 0) {
            ECTouch::err()->add(L('invalid_order_id'));

            return false;
        }
        $order = model('Order')->order_info($order_id);



        //检查订单是否属于该用户
        if ($user_id > 0 && $user_id != $order['parent_id']) {
            ECTouch::err()->add(L('no_priv'));

            return false;
        }

        /* 对发货号处理 */
        if (!empty($order['invoice_no'])) {
            $sql = "SELECT shipping_code FROM " . $this->pre . "shipping WHERE shipping_id = '$order[shipping_id]'";
            $res = $this->row($sql);
            $shipping_code = $res['shipping_code'];
            $plugin = ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';
            if (file_exists($plugin)) {
                include_once($plugin);
                $shipping = new $shipping_code;
                $order['invoice_no'] = $shipping->query($order['invoice_no']);
            }
        }


        $order['allow_update_address'] = 0;


        /* 获取订单中实体商品数量 */
        $order['exist_real_goods'] = model('Order')->exist_real_goods($order_id);


        /* 无配送时的处理 */
        $order['shipping_id'] == -1 and $order['shipping_name'] = L('shipping_not_need');

        /* 其他信息初始化 */
        $order['how_oos_name'] = $order['how_oos'];
        $order['how_surplus_name'] = $order['how_surplus'];


        /* 确认时间 支付时间 发货时间 */
        if ($order['confirm_time'] > 0 && ($order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED || $order['order_status'] == OS_SPLITING_PART)) {
            $order['confirm_time'] = sprintf(L('confirm_time'), local_date(C('time_format'), $order['confirm_time']));
        } else {
            $order['confirm_time'] = '';
        }
        if ($order['pay_time'] > 0 && $order['pay_status'] != PS_UNPAYED) {
            $order['pay_time'] = sprintf(L('pay_time'), local_date(C('time_format'), $order['pay_time']));
        } else {
            $order['pay_time'] = '';
        }
        if ($order['shipping_time'] > 0 && in_array($order['shipping_status'], array(SS_SHIPPED, SS_RECEIVED))) {
            $order['shipping_time'] = sprintf(L('shipping_time'), local_date(C('time_format'), $order['shipping_time']));
        } else {
            $order['shipping_time'] = '';
        }

        return $order;
    }

    // 根据id获取用户名

    function get_user_by_id($user_id){

        $sql = "SELECT user_name FROM " . $this->pre . "users WHERE user_id = '$user_id'";
        $info = $this->row($sql);
        return $info['user_name'] ? $info['user_name'] : '';
    }

    /**
     * 获取用户下线商品数量
     * @return Ambigous <number, unknown>
     */
    function get_sale_goods_count(){
        $global = getInstance();
        $sql =  "select order_id from " . $global->ecs->table("order_info") . " where parent_id=".$_SESSION['user_id'];
        $arr_order_id = $global->db->getAll($sql);
        if($arr_order_id){
            $order_id = '';
            foreach($arr_order_id as $key=>$val){
                $order_id.=$val['order_id'].',';
            }
            $order_id = substr($order_id,0,-1);
            $sql = "select sum(goods_number) as count from ".$global->ecs->table("order_goods")." where order_id in (".$order_id.")";
            $res = M()->query($sql);
            return $res['0']['count'] > 0 ? $res['0']['count'] : 0;
        }else{
            return 0;
        }
    }


    /**
     * 获取用户列表信息
     */
    function get_sale_info($key=false){
        if(!$key) return false;
        $res = array();
        if($key == 'wfk'){
            $sql = "SELECT user_id FROM {pre}order_info where user_id > 0 and pay_status = ".PS_UNPAYED." and parent_id = " . $_SESSION['user_id'] . " GROUP BY user_id";
            $user_list = M()->query($sql);
            if($user_list){
                foreach($user_list as $key=>$val){
                    $sql = "SELECT count(*) as count FROM {pre}order_info where user_id = ".$val['user_id']." and pay_status != ".PS_UNPAYED."and parent_id = " . $_SESSION['user_id'] . " GROUP BY user_id";
                    if(M()->getOne($sql) > 0){
                        unset($user_list[$key]);
                    }else{
                        $info = $this->get_drp($val['user_id']);
                        $user_list[$key] = $info;
                    }
                }
            }
            $res['count'] = count($user_list);
            $res['list'] = $user_list;
        }elseif($key == 'yfk'){
            $sql = "SELECT user_id FROM {pre}order_info where user_id > 0 and pay_status = ".PS_PAYED." and parent_id = " . $_SESSION['user_id'] . " GROUP BY user_id";
            $user_list =  M()->query($sql);
            if($user_list){
                foreach($user_list as $key=>$val){
                    $info = $this->get_drp($val['user_id']);
                    $user_list[$key] = $info;
                }
            }
            $res['count'] = count($user_list);
            $res['list'] = $user_list;
        }elseif($key == 'gz'){
            $sql = "SELECT user_id FROM {pre}users  as u join {pre}wechat_user as w on u.user_id=w.ect_uid where u.user_id > 0 and w.subscribe = 1 and u.parent_id = " . $_SESSION['user_id'] . " GROUP BY user_id";
            $user_list =  M()->query($sql);
            if($user_list){
                foreach($user_list as $key=>$val){
                    $user_list[$key] = $this->get_drp($val['user_id']);
                }
            }
            $res['count'] = count($user_list);
            $res['list'] = $user_list;
        }elseif($key == 'fk'){
            $sql = "SELECT user_id FROM {pre}users   where parent_id = " . $_SESSION['user_id'] . " GROUP BY user_id";
            $user_list =  M()->query($sql);
            if($user_list){
                foreach($user_list as $key=>$val){
                    if(M()->table('order_info')->where('user_id='.$val['user_id'])->count()){
                        unset($user_list[$key]);
                    }else{
                        $user_list[$key] = $this->get_drp($val['user_id']);
                    }
                }
            }
            $res['count'] = count($user_list);
            $res['list'] = $user_list;
        }else{
            return false;
        }

        return $res;


    }


    /**
     * 获取用户一级下线数量
     * @return Ambigous <number, unknown>
     */
    function get_line_count($user_id=0){
        $user_id = $user_id > 0 ? $user_id : session('user_id');
        $count = M()->table('users')->field('COUNT(*)')->where("parent_id = ".$user_id)->getField();
        return $count > 0 ? $count : 0;
    }


    /**
     * 获取用户下线
     * @param int $num   每页显示数量
     * @param int $start 每页开始显示条数
     */
    function saleList($uid=0){
        $uid = $uid > 0 ? $uid : session("user_id");
        $sql = "SELECT * FROM {pre}users WHERE parent_id = " .$uid . " ORDER BY user_id DESC ";
        $res = $this->query($sql);

        if (empty($res)) {
            return array();
            exit;
        }

        foreach ($res as $k => $v) {
            $res[$k]['user_id']     =   $v['user_id'];
            $res[$k]['user_name']   =   $v['user_name'];
            $res[$k]['reg_time']    =   local_date('Y-m-d H:i:s', $v['reg_time']);
            $res[$k]['mobile_phone']    =    $v['mobile_phone'] ? substr_replace($v['mobile_phone'],'****',3,4) : '';

            if(class_exists('WechatController')){
                if (method_exists('WechatController', 'get_avatar')) {
                    $u_row = call_user_func(array('WechatController', 'get_avatar'), $v['user_id']);
                }
            }
            if ($u_row) {
                $res[$k]['username'] = $u_row['nickname'];
                $res[$k]['headimgurl'] = $u_row['headimgurl'];
            } else {
                $res[$k]['username'] = $v['username'];
                $res[$k]['headimgurl'] = ____ . '/images/get_avatar.png';
            }
        }

        return $res;
    }

    /**
     * 获取用户下线
     * @param int $num   每页显示数量
     * @param int $start 每页开始显示条数
     */
    function saleuser($uid=0){
        $uid = $uid > 0 ? $uid : $_SESSION['user_id'];
        $sql = "SELECT * FROM {pre}users WHERE parent_id = " .$uid . " ORDER BY user_id DESC ";
        $res = M()->query($sql);
        if($res){
            foreach ($res as $k => $v) {
                $list[$k]['user_id']     =   $v['user_id'];
            }
            return $list;
        }else{
            return array();
        }
    }

    /**
     * 获取用户中心默认页面所需的数据
     * @access  public
     * @param   int         $user_id            用户ID
     * @return  array       $info               默认页面所需资料数组
     */
    public function get_drp($user_id,$is_drp=0) {

        if($is_drp != 0){
            $sql = "SELECT user_id FROM {pre}drp_shop WHERE id = '$user_id'";
            $row = $this->row($sql);
            $user_id = $row['user_id'];
            if(!$user_id){
                return array();exit;
            }
        }
        $sql = "SELECT pay_points, user_money, credit_line, last_login, is_validated,user_name FROM " . $this->pre . "users WHERE user_id = '$user_id'";
        $row = $this->row($sql);
        $info = array();
        //新增获取用户头像，昵称
        $u_row = '';
        if(class_exists('WechatController')){
            if (method_exists('WechatController', 'get_avatar')) {
                $u_row = call_user_func(array('WechatController', 'get_avatar'), $user_id);
            }
        }
        if ($u_row) {
            $info['username'] = $u_row['nickname'];
            $info['headimgurl'] = $u_row['headimgurl'];
        } else {
            $info['username'] = $row['user_name'];
            $info['headimgurl'] = __PUBLIC__ . '/images/get_avatar.png';
        }
        $sql = "SELECT * FROM " . $this->pre . "drp_shop WHERE user_id = '$user_id'";
        $row = $this->row($sql);
        $info['drp_id'] = $row['id'];
        $info['shop_name'] = $row['shop_name'];
        $info['real_name'] = $row['real_name'];
        $info['open']      = $row['open'];
        $info['cat_id']    = $row['cat_id'];
        $info['shop_img']    = $row['shop_img'] ? './data/attached/drp_logo/'.$row['shop_img'] : '';
        $info['user_id']   = $user_id;

        //如果$_SESSION中时间无效说明用户是第一次登录。取当前登录时间。
        $last_time = !isset($_SESSION['last_time']) ? $row['last_login'] : $_SESSION['last_time'];

        if ($last_time == 0) {
            $_SESSION['last_time'] = $last_time = gmtime();
        }

        $info['time'] = local_date(C('time_format'), $last_time);

        return $info;
    }

    /**
     * 获取佣金比例
     * @param $goods_id
     */
    public function get_drp_profit($goods_id=0){
        if($goods_id == 0 ){
            return false;
        }
        $id = M()->table('goods')->field('cat_id')->where("goods_id=$goods_id")->getOne();
        $id = $this->get_goods_cat($id);
        $profit = M()->table('drp_profit')->where('cate_id='.$id)->select();
        return $profit['0'];
    }

    public function get_goods_cat($id){
        $parent_id = M()->table('category')->field('parent_id')->where("cat_id=$id")->getOne();
        if($parent_id==0){
            return $id;
        }else{
            $id = $this->get_goods_cat($parent_id);
            return $id;
        }
    }

    /**
     * 获取店铺销售总额
     */
    public function get_shop_sale_money($user_id=0,$separate=0){
        // 定义返回数组
        $data = array(
            'profit'=>0,
            'profit1'=>0,
            'profit2'=>0,
            'profit_num'=>0,
        );

        $profit = array(
            'profit1' => 0,
            'profit2' => 0,
            'profit3' => 0,
        );
        // 本店销售佣金
        $drp_id = M()->table('drp_shop')->field('id')->where("user_id=".$user_id)->getOne();
        $order_id = M()->table('order_info')->field('order_id')->where('drp_id='.$drp_id ." and shop_separate=".$separate)->select();
        $where = "0";
        foreach($order_id as $key=>$val){
            $where.=",".$val['order_id'];
        }
        $goods_list = M()->table('order_goods')->where('order_id in('.$where.')')->select();

        foreach($goods_list as $key=>$val){
            $profit = $this->get_drp_profit($val['goods_id']);
            if(!$profit['profit1']){
                $profit['profit1'] = 0;
            }
            // 一级分销商利润
            $data['profit']+= $val['touch_sale']*$profit['profit1']/100*$val['goods_number'];
        }
        //一级分店
        $sql = "select d.id from {pre}drp_shop as d JOIN {pre}users as u on d.user_id=u.user_id where u.parent_id=".$user_id." and apply_sale = 1";
        $drp_list = M()->query($sql);
        if($drp_list){
            $where_drp = '-1';
            foreach($drp_list as $key=>$val){
                $where_drp.=",".$val['id'];
            }
            $order_id = M()->table('order_info')->field('order_id')->where('drp_id in('.$where_drp.') and shop_separate='.$separate)->select();

            if($order_id){
                $where = "0";
                foreach($order_id as $key=>$val){
                    $where.=",".$val['order_id'];
                }
                $goods_list = M()->table('order_goods')->where('order_id in('.$where.')')->select();

                foreach($goods_list as $key=>$val){
                    $profit = $this->get_drp_profit($val['goods_id']);
                    if(!$profit['profit2']){
                        $profit['profit2'] = 0;
                    }
                    // 一级分销商利润
                    $data['profit1']+= $val['touch_sale']*$profit['profit2']/100*$val['goods_number'];
                }
                //二级分店
                $sql = "select d.id from {pre}drp_shop as d JOIN {pre}users as u on d.user_id=u.user_id where u.parent_id in (".$where_drp.") and apply_sale = 1";
                $drp_list = M()->query($sql);
                if($drp_list){
                    $where_drp = '-1';
                    foreach($drp_list as $key=>$val){
                        $where_drp.=",".$val['id'];
                    }
                    $order_id = M()->table('order_info')->field('order_id')->where('drp_id in('.$where_drp.') and shop_separate='.$separate)->select();
                    if($order_id){
                        $where = "0";
                        foreach($order_id as $key=>$val){
                            $where.=",".$val['order_id'];
                        }
                        $goods_list = M()->table('order_goods')->where('order_id in('.$where.')')->select();

                        foreach($goods_list as $key=>$val){
                            $profit = $this->get_drp_profit($val['goods_id']);
                            if(!$profit['profit3']){
                                $profit['profit3'] = 0;
                            }
                            // 一级分销商利润
                            $data['profit2']+= $val['touch_sale']*$profit['profit3']/100*$val['goods_number'];
                        }
                    }

                }
            }

        }
        foreach($data as $Key=>$val){
            $data['profit_num']+=$val;
        }
        return $data;
    }

    /**
     * 获取分成佣金
     */
    public function get_user_separate_money($user_id=0,$separate=0){
        $user_id = $user_id > 0 ? $user_id : session('user_id');
        $affiliate = unserialize(C('affiliate'));
        empty($affiliate) && $affiliate = array();

        $separate_by = $affiliate['config']['separate_by'];

        // 获取一级订单
        $order_id = M()->table('order_info')->field('order_id')->where('parent_id='.$user_id ." and is_separate=".$separate)->select();
        $where = "0";
        foreach($order_id as $key=>$val){
            $where.=",".$val['order_id'];
        }
        $goods_list = M()->table('order_goods')->where('order_id in('.$where.')')->select();
        $money1=0;
        foreach($goods_list as $Key=>$val){
            $money1+= (float)$affiliate['item']['1']['level_money']*$val['touch_profit']/100;
        }
        // 二级
        $user_list = $this->saleuser($user_id);
        if($user_list){
            $where_user = '-1';
            foreach($user_list as $key=>$val){
                $where_user.=",".$val['user_id'];
            }
            $order_id = M()->table('order_info')->field('order_id')->where("parent_id in(".$where_user.") and is_separate=".$separate)->select();
            $where = "0";
            foreach($order_id as $key=>$val){
                $where.=",".$val['order_id'];
            }
            $goods_list = M()->table('order_goods')->where('order_id in('.$where.')')->select();
            $money2=0;
            foreach($goods_list as $Key=>$val){
                $money2+= (float)$affiliate['item']['2']['level_money']*$val['touch_profit']/100;
            }
            // 三级
            $user_list = M()->table('users')->field('user_id')->where("parent_id in(".$where_user.")")->select();
            if($user_list){
                $where_user = '-1';
                foreach($user_list as $key=>$val){
                    $where_user.=",".$val['user_id'];
                }
                $order_id = M()->table('order_info')->field('order_id')->where("parent_id in(".$where_user.") and is_separate=".$separate)->select();
                $where = "0";
                foreach($order_id as $key=>$val){
                    $where.=",".$val['order_id'];
                }
                $goods_list = M()->table('order_goods')->where('order_id in('.$where.')')->select();
                $money3=0;
                foreach($goods_list as $Key=>$val){
                    $money3+= (float)$affiliate['item']['3']['level_money']*$val['touch_profit']/100;
                }
            }else{
                $money3 = 0;
            }

        }else{
            $money2 =   0;
        }
        $data['money1'] = $money1 ? $money1 : 0;
        $data['money2'] = $money2 ? $money2 : 0;
        $data['money3'] = $money3 ? $money3 : 0;
        return $data;
    }


    /**
     * 根据银行卡id获取银行卡信息
     * @param $bank_id
     */
    public function get_bank_info($bank_id=0){
        if($bank_id==0){
            return false;
        }
        $bank_info = $this->model->table('drp_bank')->where("id=$bank_id")->select();
        return $bank_info['0'];
    }

}
