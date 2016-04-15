<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：test.php
 * ----------------------------------------------------------------------------
 * 功能描述：Test入口文件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

define('ROOT_PATH', str_replace('\\', '/', realpath(dirname(__FILE__) . '/../')) . '/');
define('BASE_PATH', ROOT_PATH . 'include/');
define('APP_PATH', BASE_PATH . 'apps/');
define('COMMON_PATH', APP_PATH . 'base/');
define('CONF_PATH', BASE_PATH . 'config/');
define('VENDOR_PATH', BASE_PATH . 'vendor/');
define('RUNTIME_PATH', ROOT_PATH . 'data/caches/');
define('APP_DEBUG', true);
define('BIND_MODULE', 'wechat');
define('BUILD_DIR_SECURE', false);
require VENDOR_PATH . 'system/start.php';
