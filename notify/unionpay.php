<?php

/**
 * ECTouch E-Commerce Project
 * ============================================================================
 * Copyright (c) 2014-2015 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/license )
 * ----------------------------------------------------------------------------
 */

define('BIND_CONTROLLER', 'Respond');
$_GET['code'] = 'unionpay'; // 支付插件名称
$_GET['type'] = 'notify'; // 异步通知：notify，同步通知：return
require dirname(dirname(__FILE__)) . '/include/bootstrap.php';
