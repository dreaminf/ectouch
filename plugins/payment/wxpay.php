<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：wxpay.php
 * ----------------------------------------------------------------------------
 * 功能描述：微信支付插件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
if (! defined('IN_ECTOUCH')) {
    die('Deny Access');
}

$payment_lang = ROOT_PATH . 'plugins/payment/language/' . C('lang') . '/' . basename(__FILE__);

if (file_exists($payment_lang)) {
    include_once ($payment_lang);
    L($_LANG);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;
    /* 代码 */
    $modules[$i]['code'] = basename(__FILE__, '.php');
    /* 描述对应的语言项 */
    $modules[$i]['desc'] = 'wxpay_desc';
    /* 是否支持货到付款 */
    $modules[$i]['is_cod'] = '0';
    /* 是否支持在线支付 */
    $modules[$i]['is_online'] = '1';
    /* 作者 */
    $modules[$i]['author'] = 'ECTOUCH TEAM';
    /* 网址 */
    $modules[$i]['website'] = 'http://mp.weixin.qq.com/';
    /* 版本号 */
    $modules[$i]['version'] = '2.5';
    /* 配置信息 */
    $modules[$i]['config'] = array(
        array(
            'name' => 'wxpay_appid',
            'type' => 'text',
            'value' => ''
        ),
        array(
            'name' => 'wxpay_appsecret',
            'type' => 'text',
            'value' => ''
        ),
        array(
            'name' => 'wxpay_paysignkey',
            'type' => 'text',
            'value' => ''
        ),
        array(
            'name' => 'wxpay_partnerid',
            'type' => 'text',
            'value' => ''
        ),
        array(
            'name' => 'wxpay_partnerkey',
            'type' => 'text',
            'value' => ''
        ),
        array(
            'name' => 'wxpay_signtype',
            'type' => 'text',
            'value' => 'sha1'
        )
    );
    
    return;
}

/**
 * 微信支付类
 */
class wxpay
{

    var $parameters; // cft 参数
    var $payments; // 配置信息
    /**
     * 生成支付代码
     *
     * @param array $order
     * 订单信息
     * @param array $payment
     * 支付方式信息
     */
    function get_code($order, $payment)
    {
        if (! defined('EC_CHARSET')) {
            $charset = 'utf-8';
        } else {
            $charset = EC_CHARSET;
        }
        $charset = strtoupper($charset);
        
        // 配置参数
        $this->payments = $payment;
        $notify_url = str_replace('mobile/', '', __URL__ . '/notify_wap_wxpay.php'); 
        // 银行通道类型
        $this->setParameter("bank_type", "WX");
        // 商品描述
        $this->setParameter("body", $order['order_sn']);
        // 商户号
        $this->setParameter("partner", $payment['wxpay_partnerid']);
        // 商户订单号
        $this->setParameter("out_trade_no", $order['order_sn'] . 'O' . $order['log_id']);
        // 订单总金额
        $this->setParameter("total_fee", $order['order_amount'] * 100);
        // 支付币种
        $this->setParameter("fee_type", "1");
        // 通知URL
        $this->setParameter("notify_url", $notify_url);
        // 订单生成的机器IP
        $this->setParameter("spbill_create_ip", real_ip());
        // 传入参数字符编码
        $this->setParameter("input_charset", $charset);
        
        // 生成jsapi支付请求json
        $jsapi = $this->create_biz_package();
        
        // wxjsbridge
        $js = '<script language="javascript">
			function callpay(){WeixinJSBridge.invoke("getBrandWCPayRequest",' . $jsapi . ',function(res){if(res.err_msg == "get_brand_wcpay_request:ok"){location.href="index.php?m=default&c=respond&a=index&code=wxpay&status=1"}else{location.href="index.php?m=default&c=respond&a=index&code=wxpay&status=0"}});}
			</script>';
        
        $button = '<div style="text-align:center"><button class="c-btn4" type="button" onclick="callpay()">微信安全支付</button></div>' . $js;
        
        return $button;
    }

    /**
     * 响应操作
     *
     * @return boolean
     */
    function respond()
    {
        if ($_GET['status'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 设置参数
     *
     * @param unknown $parameter 
     * @param unknown $parameterValue 
     */
    function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     * 处理字符串
     *
     * @param unknown $value 
     * @return Ambigous <NULL, unknown>
     */
    function trimString($value)
    {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }
        return $ret;
    }

    /**
     * 参数判断
     *
     * @return boolean
     */
    function check_cft_parameters()
    {
        if ($this->parameters["bank_type"] == null || $this->parameters["body"] == null || $this->parameters["partner"] == null || $this->parameters["out_trade_no"] == null || $this->parameters["total_fee"] == null || $this->parameters["fee_type"] == null || $this->parameters["notify_url"] == null || $this->parameters["spbill_create_ip"] == null || $this->parameters["input_charset"] == null) {
            return false;
        }
        return true;
    }

    /**
     * 格式化参数
     *
     * @param unknown $paraMap 
     * @param unknown $urlencode 
     * @return string
     */
    function formatQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v && "sign" != $k) {
                if ($urlencode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    /**
     * 生成签名
     *
     * @param string $content 
     * @param string $key 
     * @throws Exception
     * @return string
     */
    function sign($content, $key)
    {
        try {
            if (null == $key) {
                throw new Exception("财付通签名key不能为空！" . "<br>");
            }
            if (null == $content) {
                throw new Exception("财付通签名内容不能为空" . "<br>");
            }
            $signStr = $content . "&key=" . $key;
            
            return strtoupper(md5($signStr));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * 生成jsapi支付请求json
     *
     * @throws Exception
     * @return string
     */
    function create_biz_package()
    {
        try {
            if ($this->check_cft_parameters() == false) {
                throw new Exception("生成package参数缺失！" . "<br>");
            }
            // 随机字符串
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $noncestr = "";
            for ($i = 0; $i < $length; $i ++) {
                $noncestr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            }
            // 生成package
            $package = '';
            try {
                if (null == $this->payments['wxpay_partnerkey'] || "" == $this->payments['wxpay_partnerkey']) {
                    throw new Exception("密钥不能为空！" . "<br>");
                }
                ksort($this->parameters);
                $unSignParaString = $this->formatQueryParaMap($this->parameters, false);
                $paraString = $this->formatQueryParaMap($this->parameters, true);
                
                $package = $paraString . "&sign=" . $this->sign($unSignParaString, $this->trimString($this->payments['wxpay_partnerkey']));
            } catch (Exception $e) {
                die($e->getMessage());
            }
            $nativeObj["appId"] = $this->payments['wxpay_appid'];
            $nativeObj["package"] = $package;
            $nativeObj["timeStamp"] = strval(time());
            $nativeObj["nonceStr"] = $noncestr;
            
            // 生成支付签名
            $paysign = '';
            foreach ($nativeObj as $k => $v) {
                $bizParameters[strtolower($k)] = $v;
            }
            try {
                if ($this->payments['wxpay_paysignkey'] == "") {
                    throw new Exception("APPKEY为空！" . "<br>");
                }
                $bizParameters["appkey"] = $this->payments['wxpay_paysignkey'];
                ksort($bizParameters);
                
                $buff = "";
                foreach ($bizParameters as $k => $v) {
                    $buff .= strtolower($k) . "=" . $v . "&";
                }
                $reqPar;
                if (strlen($buff) > 0) {
                    $reqPar = substr($buff, 0, strlen($buff) - 1);
                }
                
                $bizString = $reqPar;
                $paysign = sha1($bizString);
            } catch (Exception $e) {
                die($e->getMessage());
            }
            $nativeObj["paySign"] = $paysign;
            $nativeObj["signType"] = $this->payments['wxpay_signtype'];
            
            return json_encode($nativeObj);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}