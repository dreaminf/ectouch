<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：teegonwxjsapi.php
 * ----------------------------------------------------------------------------
 * 功能描述：天工微信（jsapi）支付插件
 * ----------------------------------------------------------------------------
 * Licensed (  http://www.teegon.com  )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

$payment_lang = BASE_PATH . 'languages/' .C('lang'). '/payment/teegonwxjsapi.php';

if (file_exists($payment_lang)) {
    include_once ($payment_lang);
    L($_LANG);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;
    /* 代码 */
    $modules[$i]['code'] = basename(__FILE__, '.php');
    /* 描述对应的语言项 */
    $modules[$i]['desc'] = 'teegonwxjsapi_desc';
    /* 是否支持货到付款 */
    $modules[$i]['is_cod'] = '0';
    /* 是否支持在线支付 */
    $modules[$i]['is_online'] = '1';
    /* 作者 */
    $modules[$i]['author'] = 'ECTouch TEAM';
    /* 网址 */
    $modules[$i]['website'] = 'http://www.ectouch.cn/';
    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';
    /* 配置信息 */
    $modules[$i]['config'] = array(
        array(
            'name' => 'client_id',
            'type' => 'text',
            'value' => ''
        ),
        array(
            'name' => 'client_secret',
            'type' => 'text',
            'value' => ''
        )
    );

    return;
}

