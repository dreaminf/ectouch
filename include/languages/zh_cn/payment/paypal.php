<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：paypal.php
 * ----------------------------------------------------------------------------
 * 功能描述：paypal支付插件语言包
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
if (! defined('IN_ECTOUCH')) {
    die('Deny Access');
}

$_LANG['paypal']                    = 'PayPal Express Checkout';
$_LANG['paypal_desc']               = 'PayPal 是更安全、快速、简单的跨国在线交易支付平台。';
$_LANG['paypal_account']            = 'PayPal帐号：';
$_LANG['paypal_account_desc']				= '<a href="http://www.paypal.com/" target="_blank">还没有PayPal帐号?马上注册</a>';


$_LANG['paypal_username']           = 'API用户名称：';
$_LANG['paypal_username_desc']			= '<a href="http://www.paypal.com/" target="_blank">如何取得?</a>';


$_LANG['paypal_password']             = 'API密码：';
$_LANG['paypal_signature']            = '签名：';

$_LANG['paypal_sandbox']             	= '环境：';
$_LANG['paypal_sandbox_range'][1] 		= '正式环境';
$_LANG['paypal_sandbox_range'][2] 		= '测试环境';

$_LANG['paypal_currency']              = '支付货币';
$_LANG['paypal_currency_range']['TWD'] = '台币';
$_LANG['paypal_currency_range']['AUD'] = '澳币';
$_LANG['paypal_currency_range']['CAD'] = '加币';
$_LANG['paypal_currency_range']['EUR'] = '欧元';
$_LANG['paypal_currency_range']['GBP'] = '英镑';
$_LANG['paypal_currency_range']['JPY'] = '日元';
$_LANG['paypal_currency_range']['USD'] = '美元';
$_LANG['paypal_currency_range']['HKD'] = '港币';
$_LANG['paypal_currency_range']['SGD'] = '新加坡币';

return $_LANG;