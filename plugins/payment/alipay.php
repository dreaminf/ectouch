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

require_once __DIR__ . "/alipay/alipay_submit.class.php";

/**
 * 支付插件类
 */
class alipay
{

    /**
     * 生成支付代码
     *
     * @param array $order
     *            订单信息
     * @param array $payment
     *            支付方式信息
     */
    function get_code($order, $payment)
    {
        if (! defined('EC_CHARSET')) {
            $charset = 'utf-8';
        } else {
            $charset = EC_CHARSET;
        }
        
        $gateway = 'http://wappaygw.alipay.com/service/rest.htm?';
        // 请求业务数据
        $req_data = '<direct_trade_create_req>' . '<subject>' . $order['order_sn'] . 'B' . $order['log_id'] . '</subject>' . '<out_trade_no>' . $order['order_sn'].time() . '</out_trade_no>' . '<total_fee>' . $order['order_amount'] . '</total_fee>' . '<seller_account_name>' . $payment['alipay_account'] . '</seller_account_name>' . '<call_back_url>' . return_url(basename(__FILE__, '.php')) . '</call_back_url>' . '<notify_url>' . return_url(basename(__FILE__, '.php'), true) . '</notify_url>' . '<out_user>' . $order['consignee'] . '</out_user>' . '<merchant_url>' . __URL__ . '</merchant_url>' . '<pay_expire>3600</pay_expire>' . '</direct_trade_create_req>';
        $parameter = array(
            'service' => 'alipay.wap.trade.create.direct', // 接口名称
            'format' => 'xml', // 请求参数格式
            'v' => '2.0', // 接口版本号
            'partner' => $payment['alipay_partner'], // 合作者身份ID
            'req_id' => $order['order_sn'] . $order['log_id'], // 请求号，唯一
            'sec_id' => 'MD5', // 签名方式
            'req_data' => $req_data, // 请求业务数据
            "_input_charset" => $charset
        );
        
        ksort($parameter);
        reset($parameter);
        $param = '';
        $sign = '';
        
        foreach ($parameter as $key => $val) {
            $param .= "$key=" . urlencode($val) . "&";
            $sign .= "$key=$val&";
        }
        
        $param = substr($param, 0, - 1);
        $sign = substr($sign, 0, - 1) . $payment['alipay_key'];
        
        // 请求授权接口
        $result = Http::doPost($gateway, $param . '&sign=' . md5($sign));
        $result = urldecode($result); // URL转码
        $result_array = explode('&', $result); // 根据 & 符号拆分
        // 重构数组
        $new_result_array = $temp_item = array();
        if (is_array($result_array)) {
            foreach ($result_array as $vo) {
                $temp_item = explode('=', $vo, 2); // 根据 & 符号拆分
                $new_result_array[$temp_item[0]] = $temp_item[1];
            }
        }
        $xml = simplexml_load_string($new_result_array['res_data']);
        $request_token = (array) $xml->request_token;
        // 请求交易接口
        $parameter = array(
            'service' => 'alipay.wap.auth.authAndExecute', // 接口名称
            'format' => 'xml', // 请求参数格式
            'v' => $new_result_array['v'], // 接口版本号
            'partner' => $new_result_array['partner'], // 合作者身份ID
            'sec_id' => $new_result_array['sec_id'],
            'req_data' => '<auth_and_execute_req><request_token>' . $request_token[0] . '</request_token></auth_and_execute_req>',
            'request_token' => $request_token[0],
            '_input_charset' => $charset
        );
        
        ksort($parameter);
        reset($parameter);
        $param = '';
        $sign = '';
        
        foreach ($parameter as $key => $val) {
            $param .= "$key=" . urlencode($val) . "&";
            $sign .= "$key=$val&";
        }
        
        $param = substr($param, 0, - 1);
        $sign = substr($sign, 0, - 1) . $payment['alipay_key'];
        
        /* 生成支付按钮 */
        $button = '<script type="text/javascript" src="'.__PUBLIC__.'/js/ap.js"></script><div class="n-flow-alipay"><input type="button" class="btn ect-btn-info ect-colorf n-btn-flow" onclick="javascript:_AP.pay(\'' . $gateway . $param . '&sign=' . md5($sign) . '\')" value="立即付款" class="c-btn3" /></div>';
        return $button;
    }
    
    public function refund($order, $payment)
    {
        $config = array(
            'partner' => trim($payment['alipay_partner']), // 合作身份者ID
            'seller_user_id' => trim($payment['alipay_account']), // 卖家支付宝账号
            'key' => trim($payment['alipay_key']), // MD5密钥，安全检验码
            'notify_url' => return_url(basename(__FILE__, '.php'), true), // 服务器异步通知页面路径
            'sign_type' => strtoupper('MD5'), // 签名方式
            'refund_date' => date("Y-m-d H:i:s",time()), // 退款日期 时间格式 yyyy-MM-dd HH:mm:ss
            'service' => 'refund_fastpay_by_platform_pwd', // 调用的接口名
            'input_charset' => strtolower('utf-8'), // 字符编码格式
            'service' => 'refund_fastpay_by_platform_pwd', // 调用的接口名
            'cacert' => getcwd().'\\cacert.pem', //ca证书路径地址
            'transport' => 'http', //访问模式,根据自己的服务器是否支持ssl访问
        );
        // 构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => trim($config['service']),
            "partner" => trim($config['partner']),
            "notify_url"	=> trim($config['notify_url']),
            "seller_user_id"	=> trim($config['seller_user_id']),
            "refund_date"	=> trim($config['refund_date']),
            "batch_no"	=> $batch_no,
            "batch_num"	=> 1,
            "detail_data"	=> $detail_data,
            "_input_charset"	=> trim(strtolower($config['input_charset']))
        );
        // 建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        //echo $html_text; //TODO 功能不支持API，跳转退款待测试
    }

    /**
     * 手机支付宝同步响应操作
     * 
     * @return boolean
     */
    public function callback($data)
    {
		if (! empty($_GET)) {
			$out_trade_no = explode('B', $_GET['subject']);
			$log_id = $out_trade_no[1];
			$payment = model('Payment')->get_payment($data['code']);

			/* 检查数字签名是否正确 */
			ksort($_GET);
			reset($_GET);
			
			$sign = '';
			foreach ($_GET as $key => $val) {
				if ($key != 'sign' && $key != 'sign_type' && $key != 'code') {
					$sign .= "$key=$val&";
				}
			}
			$sign = substr($sign, 0, - 1) . $payment['alipay_key'];
			if (md5($sign) != $_GET['sign']) {
				return false;
			}
			
			if ($_GET['result'] == 'success') {
				/* 改变订单状态 */
				model('Payment')->order_paid($log_id, 2);
				return true;
			} else {
				return false;
			}
		}else{
			return false;
		}
	}

    /**
     * 手机支付宝异步通知
     * 
     * @return string
     */
    public function notify($data)
    {
        if (! empty($_POST)) {
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
            $out_trade_no = explode('B', $data['subject']);
            $log_id = $out_trade_no[1]; // 订单号log_id
            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                /* 改变订单状态 */
                model('Payment')->order_paid($log_id, 2);
                exit("success");
            } else {
                exit("fail");
            }
        } else {
            exit("fail");
        }
    }
}
