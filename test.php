<?php

/**
 * ECTouch E-Commerce Project
 * ============================================================================
 * Copyright (c) 2014-2016 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/license )
 * ----------------------------------------------------------------------------
 */

if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    die('require PHP > 5.3.0 !');
}
defined('APP_DEBUG') or define('APP_DEBUG', true);
// 系统信息
defined('APPNAME') or define('APPNAME', 'ECTouch');
defined('VERSION') or define('VERSION', '2.4.1');
defined('RELEASE') or define('RELEASE', '20160201');
defined('CHARSET') or define('CHARSET', 'utf-8');
// 系统目录
defined('ROOT_PATH') or define('ROOT_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
defined('BASE_PATH') or define('BASE_PATH', ROOT_PATH . 'include/');
defined('APP_PATH') or define('APP_PATH', BASE_PATH . 'apps/');
defined('CONF_PATH') or define('CONF_PATH', BASE_PATH . 'config/');
defined('COMMON_PATH') or define('COMMON_PATH', BASE_PATH . 'common/');
defined('ADDONS_PATH') or define('ADDONS_PATH', BASE_PATH . 'modules/');
defined('VENDOR_PATH') or define('VENDOR_PATH', BASE_PATH . 'vendor/');
defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'data/caches/');
defined('BUILD_DIR_SECURE') or define('BUILD_DIR_SECURE', false);
defined('PHP_SELF') or define('PHP_SELF', substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1));
// 启动应用
require VENDOR_PATH . 'system/autoload.php';
