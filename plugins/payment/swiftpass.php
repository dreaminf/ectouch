<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：Swiftpass.php
 * ----------------------------------------------------------------------------
 * 功能描述：手机威富通支付插件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

/**
 * 支付插件类
 */
class Swiftpass
{
    /**
     * 生成支付代码
     * @param array $order
     *            订单信息
     * @param array $payment
     *            支付方式信息
     */
    function get_code($order, $payment)
    {
        $gateway = 'https://pay.swiftpass.cn/pay/gateway';
        $gatewayjs = 'https://pay.swiftpass.cn/pay/jspay';
        $swiftkey = $payment['swiftpass_key'];

        // 网页授权获取用户openid
        $openid = empty($_SESSION['openid']) ? $_SESSION['wechat_user']['openid'] : $_SESSION['openid'];
        if (!isset($openid) || empty($openid)) {
            return false;
        }
        // 请求业务数据

        $parameter = array(
            'service' => 'pay.weixin.jspay', // 接口名称
            'charset' => 'UTF-8', // 字符集
            'mch_id' => $payment['swiftpass_account'], // 商户号
            'out_trade_no' => $order['order_sn'], // 商户系统订单号
            'body' => $order['order_sn'] . 'B' . $order['log_id'],
            'mch_create_ip' => '0.0.0.0',
            'sub_openid' => $openid,
            'total_fee' => '1',  //$order['total_fee']
            'nonce_str' => mt_rand(),
            'callback_url' => return_url(basename(__FILE__, '.php')),
            'notify_url' => return_url(basename(__FILE__, '.php'), true),
        );

        ksort($parameter);
        reset($parameter);
        $sign = '';

        foreach ($parameter as $key => $val) {
            $sign .= "$key=$val&";
        }

        $sign = strtoupper(md5($sign . 'key=' . $swiftkey));
        $parameter['sign'] = $sign;


        $req_data = $this->arrayToXml($parameter);
        $res = $this->postXml($gateway, $req_data);
        $response = $this->xmlToArray2($res);
        if(!$response['token_id']){
            return false;
        }
        $url = $gatewayjs . '?token_id=' . $response['token_id'] . '&showwxtitle=1';

        $button = '<div style="text-align:center"><button class="btn btn-info ect-btn-info ect-colorf ect-bg" style="background-color:#44b549;" type="button"><a href="' . $url . '" style="text-decoration:none;color:#000;">立即付款</a></button></div>';

        return $button;
    }
    // 数组转xml
    public function arrayToXml($array)
    {
        $returnStr = "<xml>";
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $returnStr .= "<".$key.">".$val."</".$key.">";
            } else {
                $returnStr .= "<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $returnStr .= "</xml>";
        return $returnStr;
    }
    public function xmlToArray2($xml) {
        $xml = simplexml_load_string($xml);
        //获取xml编码
        $ret = preg_match ("/<?xml[^>]* encoding=\"(.*)\"[^>]* ?>/i", $xml, $arr);
        if($ret) {
            $encode = strtoupper ( $arr[1] );
        } else {
            $encode = "";
        }
        if($xml && $xml->children()) {
            foreach ($xml->children() as $node){
                //有子节点
                if($node->children()) {
                    $k = $node->getName();
                    $nodeXml = $node->asXML();
                    $v = substr($nodeXml, strlen($k)+2, strlen($nodeXml)-2*strlen($k)-5);

                } else {
                    $k = $node->getName();
                    $v = (string)$node;
                }

                if($encode!="" && $encode != "UTF-8") {
                    $k = iconv("UTF-8", $encode, $k);
                    $v = iconv("UTF-8", $encode, $v);
                }

                $parameters[$k] = $v;
            }
        }
        return $parameters;
    }


    function postXml($url, $array)
    {
        // curl初始化设置
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 14);  // 设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
        // 运行curl
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * 手机支付宝同步响应操作
     *
     * @return boolean
     */
    public function callback($data)
    {
        return true;
    }

    /**
     * 手机支付宝异步通知
     *
     * @return string
     */
    public function notify($data)
    {
        $xml = file_get_contents('php://input');
        if (!empty($xml)) {
            $payment = model('Payment')->get_payment($data['code']);
            // 参数
            $parameter = $this->xmlToArray2($xml);
            // 生成签名字符串
            $signPars = "";
            ksort($parameter);
            foreach($parameter as $k => $v) {
                if("sign" != $k && "" != $v) {
                    $signPars .= $k . "=" . $v . "&";
                }
            }
            $signPars .= "key=" . $payment['swiftpass_key'];
            $sign = strtolower(md5($signPars));
            $tenpaySign = strtolower($parameter["sign"]);

            // 验证签名
            if ($sign != $tenpaySign) {
                exit("fail");
            }

            // 获取支付订单号log_id
            $out_trade_no = $parameter['out_trade_no'];
            $log_id = model('Payment')->get_log_id($out_trade_no); // 订单号log_id
            if($parameter['status'] == 0 && $parameter['result_code'] == 0){
                /* 改变订单状态 */
                model('Payment')->order_paid($log_id, 2);
                exit("success");
            }else {
                exit("fail");
            }

        } else {
            exit("fail");
        }
    }
}
