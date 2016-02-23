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
class alipay{
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
+            'partner' => trim($payment['alipay_partner']), // 合作者身份ID
+			"out_trade_no"  => $order['order_sn'] . $order['log_id'],   //商户网站唯一订单号
+			//"currency"    => $order['currency'],    //币种
+			//"subject"     => $order['subject'],     //商品名称
+            "_input_charset" => trim(strtolower($payment['input_charset']))  //编码格式
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
        $button = '<script type="text/javascript" src="'.__PUBLIC__.'/js/ap.js"></script><div><input type="button" class="btn btn-info ect-btn-info ect-colorf ect-bg" onclick="javascript:_AP.pay(\'' . $gateway . $param . '&sign=' . md5($sign) . '\')" value="去付款" class="c-btn3" /></div>';
        return $button;
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
 
