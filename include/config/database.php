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

$global_config = ROOT_PATH . 'data/config.php';
if (file_exists($global_config)) {
    require $global_config;
    $db_hosts = explode(':', $db_host);
    $db_host = $db_hosts[0];
    $db_port = isset($db_hosts[1]) ? $db_hosts[1] : '3306';
} else {
    die('<p style="font-size:14px;color:red;">ECTouch Info：无法连接到MySQL数据库，请将ecshop的 data/config.php 文件复制到 mobile/data 目录下。</p>');
}

return array(
    'DB_TYPE' => 'mysql',
    'DB_HOST' => $db_host,
    'DB_USER' => $db_user,
    'DB_PWD' => $db_pass,
    'DB_NAME' => $db_name,
    'DB_PREFIX' => $prefix,
    'DB_PORT' => $db_port,
    'DB_CHARSET' => 'utf8'
);
