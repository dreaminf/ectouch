<?php

/**
 * ECSHOP API 公用初始化文件
*/
if (! defined('IN_ECTOUCH')) {
    die('Hacking attempt');
}
defined('BASE_PATH') or define('BASE_PATH', dirname(__FILE__) . '/../include/');
defined('ROOT_PATH') or define('ROOT_PATH', realpath(dirname(__FILE__) . '/../') . '/');
/* 加载默认配置 */
require (BASE_PATH . 'EcConfig.class.php');
/* 加载默认配置 */
require (BASE_PATH . 'Common.php');
/* 加载常规配置 */
C(load_file(BASE_PATH . 'Convention.php'));
/* 设置时区 */
date_default_timezone_set(EcConfig::get('TIMEZONE'));

require (ROOT_PATH . 'include/base/function.php');

/* 初始化数据库类 */
require (ROOT_PATH . 'include/EcModel.class.php');
$db = new EcModel(C('DB'));

/* 初始化session */
require (ROOT_PATH . 'include/library/EcsApiSession.class.php');
$sess_name = defined("SESS_NAME") ? SESS_NAME : 'ECS_ID';
$sess = new EcsApiSession($db, $db->pre .'sessions', $db->pre . 'sessions_data', $sess_name);

/* 载入系统参数 */
$_CFG = load_config();
C('CFG', $_CFG);

/* 初始化用户插件 */
$user = init_users();

header('Content-type: text/html; charset=' . EC_CHARSET);

?>