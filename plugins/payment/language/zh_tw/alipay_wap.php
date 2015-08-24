<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：alipay_wap.php
 * ----------------------------------------------------------------------------
 * 功能描述：手机支付宝支付插件语言包
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
if (! defined('IN_ECTOUCH')) {
    die('Deny Access');
}

$_LANG['alipay_wap'] = '支付寶（手機版）';
$_LANG['alipay_wap_desc'] = '支付寶（手機版）(www.alipay.com) 是國內先進的支付平臺';
$_LANG['alipay_account'] = '支付寶帳號';
$_LANG['alipay_key'] = '交易安全校驗碼';
$_LANG['alipay_partner'] = '合作者身份ID';
$_LANG['pay_button'] = '立即使用支付寶支付';


$_LANG['relate_pay'] = '關聯電腦支付方式';
$_LANG['relate_pay_desc'] = '請選擇關聯電腦版支付方式，用於電腦版支付；不關聯則使用默認的支付方式。';
$_LANG['relate_pay_range'] = $pc_pay_type;