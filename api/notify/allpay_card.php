<?php

/**
 * ECTouch E-Commerce Project
 * ============================================================================
 * Copyright (c) 2012-2015 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：allpay_card.php
 * ----------------------------------------------------------------------------
 * 手机欧付宝支付异步通知处理
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

define('IN_ECTOUCH', true);
define('ROOT_PATH', str_replace('\\', '/', dirname(dirname(dirname(__FILE__)))) . '/');
define('CONTROLLER_NAME', 'Respond');
$_GET['code'] = 'allpay_card';
$_GET['type'] = 'notify';
$_GET['background'] = '1';
require ROOT_PATH . 'include/bootstrap.php';


