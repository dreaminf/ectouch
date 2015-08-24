<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * File name：alipay_wap.php
 * ----------------------------------------------------------------------------
 * Function description：alipay_wap payment language file
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
if (! defined('IN_ECTOUCH')) {
    die('Deny Access');
}

$_LANG['alipay_wap'] = 'alipay（wap）';
$_LANG['alipay_wap_desc'] = 'alipay（wap）(www.alipay.com) Is the domestic advanced online payment platform.';
$_LANG['alipay_account'] = 'alipay account';
$_LANG['alipay_key'] = 'Transaction security check code';
$_LANG['alipay_partner'] = 'Partner identity ID';
$_LANG['pay_button'] = 'Immediate use Alipay payment';


$_LANG['relate_pay'] = 'Related computer payment method';
$_LANG['relate_pay_desc'] = 'Please choose the associated computer version of the payment method for the computer version of the payment is not associated with the use of the default payment method.';
$_LANG['relate_pay_range'] = $pc_pay_type;