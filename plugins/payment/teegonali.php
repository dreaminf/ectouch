<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：alipay_wap.php
 * ----------------------------------------------------------------------------
 * 功能描述：手机支付宝支付插件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

/**
 * 类
 */
class teegonali
{

    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code($order, $payment)
    {

        $name = model('Payment')->get_goods_name_by_id($order['order_id']);
        $param['order_no'] = $order['order_sn']; //订单号
        $param['channel'] = 'alipay';
        $param['return_url'] = return_url(basename(__FILE__, '.php'));
       // $param['return_url'] = 'http://www.qq.com';
        $param['amount'] = $order['order_amount'];
        $param['subject'] =$name;
        $param['metadata'] = "teegonali";
        //$param['notify_url'] = 'http://www.baidu.com';//支付成功后天工支付网关通知
        $param['notify_url'] = return_url(basename(__FILE__, '.php'));
        $param['client_ip'] = $_SERVER["REMOTE_ADDR"];
        $param['client_id'] = $payment['teegonali_client_id'];
        $param['sign'] = $this->sign($param,$payment);


//        error_log(print_r($order,1)."\n~~~~",3,"/Users/roshan/www/ecshop/admin/ecshop.log");
//        error_log(print_r($param,1)."\n~~~~",3,"/Users/roshan/www/ecshop/admin/ecshop.log");
        //error_log(print_r($payment,1)."\n~~~~",3,"/Users/roshan/www/ecshop/admin/ecshop.log");

        $def_url  = '<div style="text-align:center"><form name="teegonali" accept-charset="UTF-8" style="text-align:center;" method="post" action="https://api.teegon.com/charge/pay" target="_blank">';
        $def_url .= "<input type='hidden' name='order_no' value='" . $param['order_no'] . "' />";
        $def_url .= "<input type='hidden' name='channel' value='" . $param['channel'] . "' />";
        $def_url .= "<input type='hidden' name='amount' value='" . $param['amount'] . "' />";
        $def_url .= "<input type='hidden' name='subject' value='" . $name . "' />";
        $def_url .= "<input type='hidden' name='metadata' value='" . $param['metadata'] . "' />";
        $def_url .= "<input type='hidden' name='client_ip' value='" . $param['client_ip'] . "' />";
        $def_url .= "<input type='hidden' name='return_url' value='" . $param['return_url'] . "' />";
        $def_url .= "<input type='hidden' name='notify_url' value='" . $param['notify_url'] . "' />";
        $def_url .= "<input type='hidden' name='sign' value='" . $param['sign'] . "' />";
        $def_url .= "<input type='hidden' name='client_id' value='" . $param['client_id'] . "' />";
        $def_url .= "<input type='submit' class=\"btn btn-info ect-btn-info ect-colorf ect-bg\" value='立即付款' />";
        $def_url .= "</form></div></br>";


        return $def_url;
    }

    /**
     * 响应操作
     */
    function callback()
    {
        if (!empty($_POST))
        {
            foreach($_POST as $key => $data)
            {
                $_GET[$key] = $data;
            }
        }
        $payment  = model('Payment')->get_payment($_GET['code']);
        $_GET['data'] = stripslashes($_GET['data']);
        //$_GET['data']=json_decode($_GET['data'],true);

        //验证签名
       // echo "<pre/>";
        unset($_GET['code']);
        $resign = $this->sign($_GET,$payment);
        //print_r($_GET);
        //print_r($resign);exit;

        //获取paid
        $pay_id = model('Payment')->get_order_id_by_sn($_GET['order_no']);

        /* 检查支付的金额是否相符 */
        if (!model('Payment')->check_money($pay_id, $_GET['amount']))
        {
            return false;
        }

        //修改订单状态
        if ($_GET['is_success'] == 'true')
        {
            /* 改变订单状态 */
            model('Payment')->order_paid($pay_id, 2);
            if(!empty($_POST))
            {
                $tgarr = array(
                    array("source_account"=>"main","target_account"=>"main","amount"=> $_GET['amount']),
                );
                $tgreturn = json_encode($tgarr);
                $tgsign = md5($tgreturn.$payment['teegonali_client_secret']);
                header('Teegon-Rsp-Sign: '.$tgsign);
                echo $tgreturn;
                exit;
            }
        //error_log(print_r($tgarr,1)."\n~~~~",3,"/Users/roshan/www/ecshop/admin/ecshop.log");
        //error_log(print_r($tgreturn,1)."\n~~~~",3,"/Users/roshan/www/ecshop/admin/ecshop.log");
        //error_log(print_r($tgsign,1)."\n~~~~",3,"/Users/roshan/www/ecshop/admin/ecshop.log");
            //}

            return true;
        }else{
            return false;
        }



    }
    /**
     * 异步通知
     * @param $data
     * @return mixed
     */
    function notify($data)
    {
        if (! empty($_POST)) {
            include_once(BASE_PATH.'helpers/payment_helper.php');
            $payment = model('Payment')->get_payment($data['code']);
            // 支付宝系统通知待签名数据构造规则比较特殊，为固定顺序。
            $parameter['service'] = $_POST['service'];
            $parameter['v'] = $_POST['v'];
            $parameter['sec_id'] = $_POST['sec_id'];
            $parameter['notify_data'] = $_POST['notify_data'];
            // 生成签名字符串
            $sign = '';
            foreach ($parameter as $key => $val) {
                $sign .= "$key=$val&";
            }
            $sign = substr($sign, 0, - 1) . $payment['alipay_key'];
            // 验证签名
            if (md5($sign) != $_POST['sign']) {
                exit("fail");
            }
            // 解析notify_data
            $data = (array) simplexml_load_string($parameter['notify_data']);
            // 交易状态
            $trade_status = $data['trade_status'];
            // 获取支付订单号log_id
            $out_trade_no = explode('O', $data['out_trade_no']);
            $log_id = $out_trade_no[1]; // 订单号log_id
            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                /* 改变订单状态 */
                model('Payment')->order_paid($log_id, 2);
                /*if(method_exists('WechatController', 'do_oauth')){
                    //如果需要，微信通知
                    $order_id = model()->table('order_info')->field('order_id')->where(array('order_sn'=>$out_trade_no[0]))->one();
                    $order_url = U('user/order/detail', array('order_id'=>$order_id), true);
                    $order_url = urlencode(base64_encode($order_url));
                    //send_wechat_message('pay_remind', '', $out_trade_no[0].' 订单已支付', $order_url, $out_trade_no[0]);
                }*/
                exit("success");
            } else {
                exit("fail");
            }
        } else {
            exit("fail");
        }
    }
//    public function respond()
//    {
//        if (!empty($_POST))
//        {
//            foreach($_POST as $key => $data)
//            {
//                $_GET[$key] = $data;
//            }
//        }
//        $payment  = get_payment($_GET['code']);
//        $seller_email = rawurldecode($_GET['seller_email']);
//        $order_sn = str_replace($_GET['subject'], '', $_GET['out_trade_no']);
//        $order_sn = trim($order_sn);
//
//        /* 检查数字签名是否正确 */
//        ksort($_GET);
//        reset($_GET);
//
//        $sign = '';
//        foreach ($_GET AS $key=>$val)
//        {
//            if ($key != 'sign' && $key != 'sign_type' && $key != 'code')
//            {
//                $sign .= "$key=$val&";
//            }
//        }
//
//        $sign = substr($sign, 0, -1) . $payment['alipay_key'];
//        //$sign = substr($sign, 0, -1) . ALIPAY_AUTH;
//        if (md5($sign) != $_GET['sign'])
//        {
//            return false;
//        }
//
//        /* 检查支付的金额是否相符 */
//        if (!check_money($order_sn, $_GET['total_fee']))
//        {
//            return false;
//        }
//
//        if ($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS')
//        {
//            /* 改变订单状态 */
//            order_paid($order_sn, 2);
//
//            return true;
//        }
//        elseif ($_GET['trade_status'] == 'TRADE_FINISHED')
//        {
//            /* 改变订单状态 */
//            order_paid($order_sn);
//
//            return true;
//        }
//        elseif ($_GET['trade_status'] == 'TRADE_SUCCESS')
//        {
//            /* 改变订单状态 */
//            order_paid($order_sn, 2);
//
//            return true;
//        }
//        else
//        {
//            return false;
//        }
//    }

//teegon 加密算法
    public function sign($para_temp,$payment){
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->para_filter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->arg_sort($para_filter);
        //生成加密字符串
        $prestr = $this->create_string($para_sort);
        $prestr = $payment['teegonali_client_secret'] .$prestr . $payment['teegonali_client_secret'];
        return strtoupper(md5($prestr));
    }


    private function para_filter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign")continue;
            else	$para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    private function arg_sort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    private function create_string($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key.$val;
        }


        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }
}
?>
