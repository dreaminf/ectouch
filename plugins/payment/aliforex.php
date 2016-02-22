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
		
		//合作身份者id，以2088开头的16位纯数字
		$alipay_config['partner']		= '2088111956092332';

		//安全检验码，以数字和字母组成的32位字符
		$alipay_config['alipay_key']			= '136nflj7uu24i7v6cheubmpy0uav4tdx';


		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


		//签名方式 不需修改
		$alipay_config['sign_type']    = strtoupper('MD5');

		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= strtolower('utf-8');

		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$alipay_config['cacert']    = getcwd().'\\cacert.pem';

		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = 'http';
		
        $gateway = 'https://mapi.alipay.com/gateway.do?';
        // 请求业务数据
        $parameter = array(
            'service' => 'create_forex_trade_wap', // 接口名称
            'partner' => trim($alipay_config['partner']), // 合作者身份ID
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
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
        $sign = substr($sign, 0, - 1) . $alipay_config['alipay_key'];
        
       
        /* 生成支付按钮 */
        $button = '<script type="text/javascript" src="'.__PUBLIC__.'/js/ap.js"></script><div><input type="button" class="btn btn-info ect-btn-info ect-colorf ect-bg" onclick="javascript:_AP.pay(\'' . $gateway . $param . '&sign=' . md5($sign) . '\')" value="去付款" class="c-btn3" /></div>';
        return $button;
		/* 生成支付按钮 */
        return $html_text;
    }
	
 }
 
 