<?php

namespace app\plugins\payment;

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == true) {
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'balance_desc';

    /* 是否货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'ECTouch Team';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.ectouch.cn';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = [];

    return;
}

/**
 * 余额支付插件
 */
class Balance
{
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    public function balance()
    {
    }

    public function __construct()
    {
        $this->balance();
    }

    /**
     * 提交函数
     */
    public function get_code()
    {
        return '';
    }

    /**
     * 处理函数
     */
    public function response()
    {
        return;
    }
}
