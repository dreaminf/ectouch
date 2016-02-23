<?php

/**
 * 文件名称：aliforex.php
 * ----------------------------------------------------------------------------
 * 功能描述：手机支付宝境外支付插件
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');
/**
 * 境外支付插件类
 */
class aliforex{
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

        $gateway = 'https://mapi.alipay.com/gateway.do?';
        // 请求业务数据
        $parameter = array(
			'service' => 'create_forex_trade_wap', // 接口名称
            'partner' => trim($payment['aliforex_partner']), // 合作者身份ID
			"_input_charset" => $charset,  //编码格式
			"sign_type"  => strtoupper('MD5'),  //签名方式
			"notify_url" => return_url(basename(__FILE__, '.php'),true),
			"return_url" => return_url(basename(__FILE__, '.php')),
			"out_trade_no"  => $order['order_sn'],   //商户网站唯一订单号
			"currency"    => strtoupper($payment['aliforex_currency']),    //币种
			"show_url"    =>  $_SERVER['SERVER_NAME'],   //返回商户链接
			"subject"     => $order['order_sn'] . 'B' . $order['log_id'],     //商品名称
			"rmb_fee"     =>  $order['order_amount'],   //商品总价RMB
        );
        ksort($parameter);
        reset($parameter);
        $param = '';
        $sign = '';

        foreach ($parameter as $key => $val) {
			if($key == 'sign' || $key == 'sign_type' || $key == '') {
				$param .= "$key=" . urlencode($val) . "&";
			}else{
				$param .= "$key=" . urlencode($val) . "&";
				$sign .= "$key=$val&";
			}
        }

        $param = substr($param, 0, - 1);
        $sign = substr($sign, 0, - 1) . $payment['aliforex_key'];

        /* 生成支付按钮 */
        $button = '<script type="text/javascript" src="'.__PUBLIC__.'/js/ap.js"></script><div><input type="button" class="btn btn-info ect-btn-info ect-colorf ect-bg" onclick="javascript:_AP.pay(\'' . $gateway . $param . '&sign=' . md5($sign) . '\')" value="去付款" class="c-btn3" /></div>';
        return $button;
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
    public function notify()
    {
        if (! empty($_POST)) {
			$payment = model('Payment')->get_payment($_POST['code']);
           
            // 生成签名字符串
            $sign = '';
            foreach ($_POST as $key => $val) {
				if($key == 'sign' || $key == 'sign_type' || $key == '') continue;
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
 
