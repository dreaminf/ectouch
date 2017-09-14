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
use Payment\Common\PayException;
use Payment\Client\Charge;
use Payment\Client\Notify;
use Payment\Client\Query;
use Payment\Config;
use Payment\Notify\PayNotifyInterface;
use Payment\Client\Refund;



/**
 * 支付插件类
 */
class alipay
{
    /**
     * 生成支付代码
     * @param $order 订单信息
     * @param $payment 支付方式
     * @return string
     */
    public function get_code($order, $payment)
    {


        // 订单信息
        $payData = array(
            'body' => $order['order_sn'],
            'subject' => $order['order_sn'],
            'order_no' => $order['order_sn'] . 'O' . $order['log_id'],
            'timeout_express' => time() + 3600 * 24,// 表示必须 24h 内付款
            'amount' => $order['order_amount'],// 单位为元 ,最小为0.01
            'return_param' => (string)$order['log_id'],// 一定不要传入汉字，只能是 字母 数字组合
            'client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',// 客户地址
            'goods_type' => 1,
            'store_id' => '',
        );

       
        try {
            $payUrl = Charge::run(Config::ALI_CHANNEL_WAP, $this->getConfig(), $payData);

        } catch (PayException $e) {
            // 异常处理
            exit($e->getMessage());
        }

        /* 生成支付按钮 */
        return '<script type="text/javascript" src="'.__PUBLIC__.'/js/ap.js"></script><a  type="button" class="box-flex btn-submit min-two-btn" onclick="javascript:_AP.pay(\'' . $payUrl . '\')">支付宝支付</a>';
        
    }

    /**
     * 同步通知
     * @param $data
     * @return mixed
     */
    public function callback($data)
    {
        if (!empty($_GET)) {
            try {
                $order = array();
                list($order['order_sn'], $order['log_id']) = explode('O', $_GET['out_trade_no']);
                return $this->query($order);

            } catch (PayException $e) {

                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 异步通知
     * @param $data
     * @return mixed
     */
    public function notify($data)
    {
        if (!empty($_POST)) {
            try {
                $callback = new OrderPaidNotify();
                $arr = array();
                $arr = $this->getConfig();
                $arr['notify_url'] = preg_replace('/\/api\/notify/','',$arr['notify_url'],1);
                $arr['return_url'] = preg_replace('/\/api\/notify/','',$arr['return_url'],1);
                $ret = Notify::run(Config::ALI_CHARGE, $arr, $callback);// 处理回调，内部进行了签名检查
                exit($ret);
            } catch (PayException $e) {
                logResult($e->getMessage());
                exit('fail');
            }
        } else {
            exit("fail");
        }
    }

    /**
     * 订单查询
     * @return mixed
     */
    public function query($order)
    {
        $data = array(
            'out_trade_no' => $order['order_sn'] . 'O' . $order['log_id'],
        );
        try { 
            $ret = Query::run(Config::ALI_CHARGE, $this->getConfig(), $data);
             if ($ret['response']['trade_state'] === Config::TRADE_STATUS_SUCC) {
                model('Payment')->order_paid($order['log_id'], 2);
                return true;
            }
        } catch (PayException $e) {
            logResult($e->getMessage());
        }

        return false;
    }

    /**
     * 获取配置
     * @return array
     */
    private function getConfig()
    {
        $payment = model('Payment')->get_payment('alipay');
        //未配置参数，避免报错
        if(empty($payment['alipay_partner']) && empty($payment['app_id']) && empty($payment['ali_public_key'])){
                return array(
                'use_sandbox' => true,
                'partner' => '2088102169252684',
                'app_id' => '2016073100130857',
                'sign_type' => 'RSA2',
                // 可以填写文件路径，或者密钥字符串  当前字符串是 rsa2 的支付宝公钥(开放平台获取)
                'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmBjJu2eA5HVSeHb7jZsuKKbPp3w0sKEsLTVvBKQOtyb7bjQRWMWBI7FrcwEekM1nIL+rDv71uFtgv7apMMJdQQyF7g6Lnn9niG8bT1ttB8Fp0eud5L97eRjFTOa9NhxUVFjGDqQ3b88o6u20HNJ3PRckZhNaFJJQzlahCpxaiIRX2umAWFkaeQu1fcjmoS3l3BLj8Ly2zRZAnczv8Jnkp7qsVYeYt01EPsAxd6dRZRw3uqsv9pxSvyEYA7GV7XL6da+JdvXECalQeyvUFzn9u1K5ivGID7LPUakdTBUDzlYIhbpU1VS8xO1BU3GYXkAaumdWQt7f+khoFoSw+x8yqQIDAQAB',
                // 可以填写文件路径，或者密钥字符串  我的沙箱模式，rsa与rsa2的私钥相同，为了方便测试
                'rsa_private_key' => 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC/z+Ue/oS0GjO2
myYrkdopw5qq6Ih/xlHBx0HBE0xA2dRinpMuZeI0LUUtN54UAUZbDz8rcaOCb0je
loeYolw54tadcIw4Q2hbdeJPplldJZyi1BDYtBJZvAveeRSidHdmBSUtOtCBXUBl
JUP3I8/R4c34Ii4Pm/K4vmhwLf/zqZAedKGhYP6m5q+p8sfBHRPy97/KluLPiSTR
FqGSRmd0IitUGK+KQ5qsAfJXyN1oVR4jBYaxfx7dWkTWmxAfNqtKfMvu2a5lH6hv
ClN+w4RUDBu3939bLjCYKcAomkv3QMquMP46m+D8Ny+3mGk5L9Ul4jyxlFTlV4L4
JM3g/02xAgMBAAECggEBALZliwseHDLnd6V9g56K41ozlzBOTv6yJ6yNPgnLwAcr
HLtq76p/V8smAVIuQTPkwnJ03S0CsumlyTVhDzAltG2XN14fWDdoYiQWxU3YccIR
shFkd2CaW5jZKLA1k1moRqHM4r1P4FYjxshn12l7tHNwtdvvJL3THcxvxABovauF
OVtznpRlnfJLjn2Lg+xNsxaYy3zL8L6nL7MXUWLKvmLiZn64PFcw7cf+9n2exRDs
wn0wDCpypGqOVVXVFeZaXTwmOoxgIUAZfAExdLtabGGCAz1lTsA0+r4DW2nSTe8C
Fy1Db+fcCTm+uQ3y6jDwuS3tB8V+PQKog3+ReZp/9sECgYEA/NEr+ln6DTy7u4rC
Wq7mixRJ1kaiAUph/hADrUwhkMiUapSMNAIXblFB+BQUjFZQmXEbcvz0Y70g9Zi9
JCXVTiDTBe7jj/FK63MU0F9KY5OducpVV+RhSpNy/i1M2qeW4gO351PpPHUpRUYr
GkYvAKktqrSOdBEWD3IeKLYDXxMCgYEAwjoavGjWzD9Xckbpb8yrQ+gHfLeWDKh7
BgvoBGagyqbzIOZU9wg3dSQ2F5eMWDxWVRGqap3fIHxcA0/VMqXG1DrvSIUC4SE8
Zys515fR00c9h3W3IugHnKgdYcV7nZrJoPZXlMjPOo39FCBnfbrUOgnKwxMlz3lV
vC6465ODhKsCgYEAmUtTuTd5kTE0O+FFO6s1iztAEjc94D5z8JNRR3EUITAeHgn4
gUiLYI7Qy1WRqA5mTMPyeuS6Ywe4xnJYrWRrVDY+/if9v7f1T5K2GirNdld5mb//
w41tGMUTQt/A7AwWRvEuP4v3rnr0DVcgp4vK0EHEuO9GOUZq8+6kLtc+cBUCgYBF
J/kzEsVAjmEtkHA33ZExqaFY1+l2clrziTPAtWYVIiK5mSmxl9xfOliER/KxzDIV
MigStEmpQH5ms3s/AGXuVVmz4aBn1rSyK2L6D9WnO9t9qv1dUW68aeOkV3OvZ1jZ
lj0S/flDaSEulGclDmvYinoGwX+aAyLy0VQIlUqj5wKBgHEUEf7YDnvw/IBnF1E4
983/7zBx9skoHhpEZsh2+1or7LIw6z0m3lsNBnK0MZZBmW/7HwOtVfhXUUPbVrOJ
di70YoMynX3gjK3LTXhzISheZgcNRKTqiJgVunPokJxQRyYcAfaQeuIm9O8cCPE1
rZpNAzCdd4NSj83UZRm3YOmC',
                'notify_url' => notify_url(basename(__FILE__, '.php')),
                'return_url' => return_url(basename(__FILE__, '.php')),
                'return_raw' => false,
            );
        }
        else
        {
            return array(
                'use_sandbox' => (bool)$payment['use_sandbox'],
                'partner' => $payment['alipay_partner'],
                'app_id' => $payment['app_id'],
                'sign_type' => $payment['sign_type'],
                // 可以填写文件路径，或者密钥字符串  当前字符串是 rsa2 的支付宝公钥(开放平台获取)
                'ali_public_key' => $payment['ali_public_key'],
                // 可以填写文件路径，或者密钥字符串  我的沙箱模式，rsa与rsa2的私钥相同，为了方便测试
                'rsa_private_key' => $payment['rsa_private_key'],
                'notify_url' => notify_url(basename(__FILE__, '.php')),
                'return_url' => return_url(basename(__FILE__, '.php')),
                'return_raw' => false,
            );
        }
        
    }
    /**
     * 退款
     * 
     */
    public function refund($order, $payment)
    { 
        $refundNo = time() . rand(1000, 9999);      
        $data = array(
            'out_trade_no' => $order['order_sn'] . 'O' . $order['log_id'],
            'refund_fee' => $order['order_amount'],
            'refund_no' => $refundNo,
        );

        try {
            $ret = Refund::run(Config::ALI_REFUND, $this->getConfig(), $data);
            if ($ret['is_success'] === 'T') {                                
                return $this->queryrefund($order,$refundNo);
            }
        } catch (PayException $e) {
            // 异常处理
            exit($e->getMessage());
        }

     }
     /**
     * 退款订单查询
     * @return mixed
     */

     public function queryrefund($order, $refundNo){
        $data = array(
            'out_trade_no' => $order['order_sn'] . 'O' . $order['log_id'],
            'trade_no' => '',
            'refund_no' => $refundNo,
        );
        try {
            $ret = Query::run(Config::ALI_CHARGE, $this->getConfig(), $data);
            if ($ret['is_success'] === 'T') {
                model('Payment')->refund_order($order, $order['log_id'], 0);
                return true;
            }

        } catch (PayException $e) {
            echo $e->errorMessage();
            exit;
        }

     }
 }

/**
 * 客户端需要继承该接口，并实现这个方法，在其中实现对应的业务逻辑
 * Class OrderPaidNotify
 */
class OrderPaidNotify implements PayNotifyInterface
{
    public function notifyProcess(array $data)
    {
        /**
         * 改变订单状态
         */
        
        $log_id = $data['return_param']; // 订单号log_id
        model('Payment')->order_paid($log_id, 2);
        return true;
    }
}
