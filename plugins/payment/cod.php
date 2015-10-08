<?php

/**
 * Class Cod
 * Desc: 货到付款插件
 * Author: carson
 * Email: wanganlin@ecmoban.com
 * Date: 20150608
 */
defined('IN_ECTOUCH') or die('Deny Access');

$payment_lang = ROOT_PATH . 'language/' .C('lang'). '/payment/cod.php';
if (file_exists($payment_lang)) {
    global $_LANG;
    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;
    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');
    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'cod_desc';
    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '1';
    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '0';
    /* 支付费用，由配送决定 */
    $modules[$i]['pay_fee'] = '0';
    /* 作者 */
    $modules[$i]['author']  = 'ECTouch TEAM';
    /* 网址 */
    $modules[$i]['website'] = 'http://www.ectouch.cn';
    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';
    /* 配置信息 */
    $modules[$i]['config']  = array();
    return;
}

class Cod implements PaymentInterface
{

    /**
     * 生成支付代码
     * @param   array $order 订单信息
     * @param   array $payment 支付方式信息
     */
    public function get_code($order, $payment)
    {
        return '';
    }
    
    /**
     * 处理函数
     */
    public function response()
    {
        return;
    }

    /**
     * 同步通知
     * @param $data
     * @return mixed
     */
    public function callback($data)
    {

    }

    /**
     * 异步通知
     * @param $data
     * @return mixed
     */
    public function notify($data)
    {

    }

    /**
     * 订单查询
     * @return mixed
     */
    public function query($order, $payment)
    {

    }


}