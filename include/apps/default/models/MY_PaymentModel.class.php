<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：PaymentModel.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTOUCH 支付模型
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class MY_PaymentModel extends PaymentModel {



    /**
     * 修改订单的支付状态
     *
     * @access  public
     * @param   string  $log_id     支付编号
     * @param   integer $pay_status 状态
     * @param   string  $note       备注
     * @return  void
     */
    function order_paid($log_id, $pay_status = PS_PAYED, $note = '') {
        /* 取得支付编号 */
        $log_id = intval($log_id);
        if ($log_id > 0) {
            /* 取得要修改的支付记录信息 */
            $sql = "SELECT * FROM " . $this->pre .
                "pay_log WHERE log_id = '$log_id'";
            $pay_log = $this->row($sql);

            if ($pay_log && $pay_log['is_paid'] == 0) {
                /* 修改此次支付操作的状态为已付款 */
                $sql = 'UPDATE ' . $this->pre .
                    "pay_log SET is_paid = '1' WHERE log_id = '$log_id'";
                $this->query($sql);

                /* 根据记录类型做相应处理 */
                if ($pay_log['order_type'] == PAY_ORDER) {
                    /* 取得订单信息 */
                    $sql = 'SELECT order_id, user_id, order_sn, consignee, address, mobile, shipping_id, extension_code, extension_id, goods_amount ' .
                        'FROM ' . $this->pre .
                        "order_info WHERE order_id = '$pay_log[order_id]'";
                    $order = $this->row($sql);
                    $order_id = $order['order_id'];
                    $order_sn = $order['order_sn'];

                    /* 修改订单状态为已付款 */
                    $sql = 'UPDATE ' . $this->pre .
                        "order_info SET order_status = '" . OS_CONFIRMED . "', " .
                        " confirm_time = '" . gmtime() . "', " .
                        " pay_status = '$pay_status', " .
                        " pay_time = '" . gmtime() . "', " .
                        " money_paid = order_amount," .
                        " order_amount = 0 " .
                        "WHERE order_id = '$order_id'";
                    $this->query($sql);

                    /* 记录订单操作记录 */
                    model('OrderBase')->order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, L('buyer'));

                    /* 如果需要，发短信 */
                    $sms_mobile = get_sms_config(get_default_smsment(), 'mobile');
                    if (C('sms_order_payed') == '1' && $sms_mobile != '') {
                        $sms = new EcsSms();
                        $sms->send($sms_mobile, sprintf(L('order_payed_sms'), $order_sn, $order['consignee'], $order['mobile']), '', 1, '','1.0' , get_default_smsment());
                    }
                    /* 如果安装微信通,订单支付成功消息提醒 */
                    if(class_exists('WechatController')) {
                        $pushData = array(
                            'first' => array('value' => '恭喜，您的订单已经支付成功'),
                            'keyword1' => array('value' => $order_sn,'color' => '#FF0000'),  // 订单号
                            'keyword2' => array('value' => '已付款','color' => '#FF0000'),   // 付款状态
                            'keyword3' => array('value' => local_date('Y-m-d H:i', gmtime()),'color' => '#FF0000'),  //付款时间
                            'keyword4' => array('value' => C('shop_name'),'color' => '#FF0000'),       // 商户名
                            'keyword5' => array('value' => $pay_log['order_amount'],'color' => '#FF0000'),             // 付款金额
                            'remark' => array('value' => '我们会尽快给您安排发货')
                        );
                        // $url = __URL__ . 'index.php?c=user&a=order_detail&order_id='.$order_id;
                        $order_url = __HOST__ . U('user/order_detail',array('order_id'=> $order_id));
                        $url = str_replace('api/notify/wxpay.php', '', $order_url);
                        pushTemplate('OPENTM204987032', $pushData, $url, $order['user_id']);
                    }
                    /* 对虚拟商品的支持 */
                    $virtual_goods = model('OrderBase')->get_virtual_goods($order_id);
                    if (!empty($virtual_goods)) {
                        $msg = '';
                        if (!model('OrderBase')->virtual_goods_ship($virtual_goods, $msg, $order_sn, true)) {
                            $pay_success = L('pay_success') . '<div style="color:red;">' . $msg . '</div>' . L('virtual_goods_ship_fail');
                            L('pay_success', $pay_success);
                        }

                        /* 如果订单没有配送方式，自动完成发货操作 */
                        if ($order['shipping_id'] == -1) {
                            /* 将订单标识为已发货状态，并记录发货记录 */
                            $sql = 'UPDATE ' . $this->pre .
                                "order_info SET shipping_status = '" . SS_SHIPPED . "', shipping_time = '" . gmtime() . "'" .
                                " WHERE order_id = '$order_id'";
                            $this->query($sql);

                            /* 记录订单操作记录 */
                            model('OrderBase')->order_action($order_sn, OS_CONFIRMED, SS_SHIPPED, $pay_status, $note, L('buyer'));
                            $integral = model('Order')->integral_to_give($order);
                            model('ClipsBase')->log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf(L('order_gift_integral'), $order['order_sn']));
                        }
                    }

                } elseif ($pay_log['order_type'] == PAY_SURPLUS) {
                    $sql = 'SELECT `id` FROM ' . $this->pre . "user_account WHERE `id` = '$pay_log[order_id]' AND `is_paid` = 1  LIMIT 1";
                    $res = $this->row($sql);
                    $res_id = $res['id'];
                    if (empty($res_id)) {
                        /* 更新会员预付款的到款状态 */
                        $sql = 'UPDATE ' . $this->pre .
                            "user_account SET paid_time = '" . gmtime() . "', is_paid = 1" .
                            " WHERE id = '$pay_log[order_id]' LIMIT 1";
                        $this->query($sql);

                        /* 取得添加预付款的用户以及金额 */
                        $sql = "SELECT user_id, amount FROM " . $this->pre .
                            "user_account WHERE id = '$pay_log[order_id]'";
                        $arr = $this->row($sql);

                        /* 修改会员帐户金额 */
                        $_LANG = array();
                        include_once(ROOT_PATH . 'languages/' . C('lang') . '/user.php');
                        model('ClipsBase')->log_account_change($arr['user_id'], $arr['amount'], 0, 0, 0, $_LANG['surplus_type_0'], ACT_SAVING);
                    }
                }
            } else {
                /* 取得已发货的虚拟商品信息 */
                $post_virtual_goods = model('OrderBase')->get_virtual_goods($pay_log['order_id'], true);

                /* 有已发货的虚拟商品 */
                if (!empty($post_virtual_goods)) {
                    $msg = '';
                    /* 检查两次刷新时间有无超过12小时 */
                    $sql = 'SELECT pay_time, order_sn FROM ' . $this->pre . "order_info WHERE order_id = '$pay_log[order_id]'";
                    $row = $this->row($sql);
                    $intval_time = gmtime() - $row['pay_time'];
                    if ($intval_time >= 0 && $intval_time < 3600 * 12) {
                        $virtual_card = array();
                        foreach ($post_virtual_goods as $code => $goods_list) {
                            /* 只处理虚拟卡 */
                            if ($code == 'virtual_card') {
                                foreach ($goods_list as $goods) {
                                    if ($info = model('OrderBase')->virtual_card_result($row['order_sn'], $goods)) {
                                        $virtual_card[] = array('goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => $info);
                                    }
                                }

                                ECTouch::view()->assign('virtual_card', $virtual_card);
                            }
                        }
                    } else {
                        $msg = '<div>' . L('please_view_order_detail') . '</div>';
                    }
                    $pay_success = L('pay_success') . $msg;
                    L('pay_success', $pay_success);
                }

                /* 取得未发货虚拟商品 */
                $virtual_goods = model('OrderBase')->get_virtual_goods($pay_log['order_id'], false);
                if (!empty($virtual_goods)) {
                    $pay_success = L('pay_success') . '<br />' . L('virtual_goods_ship_fail');
                    L('pay_success', $pay_success);
                }
            }

        }
    }

    /**
     * @param $log_id
     * @param int $pay_status
     * @param string $note
     * 购买分销商支付处理
     */
    public function drp_order_paid($log_id, $pay_status = PS_PAYED, $note = ''){
        /* 取得支付编号 */
        $log_id = intval($log_id);
        if ($log_id > 0) {
            $data['apply'] = 2;
            $data['time'] = gmtime();
            $where['id'] = $log_id;
            $this->model->table('drp_apply')->data($data)->where($where)->update();

        }
    }

}
