<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$shipping_lang = ROOT_PATH.'include/language/' . C('lang') . '/shipping/city_express.php';
if (file_exists($shipping_lang))
{
    global $_LANG;
    include_once($shipping_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = (isset($modules)) ? count($modules) : 0;

    /* 配送方式插件的代码必须和文件名保持一致 */
    $modules[$i]['code']    = 'city_express';

    $modules[$i]['version'] = '1.0.0';

    /* 配送方式的描述 */
    $modules[$i]['desc']    = 'city_express_desc';

    /* 配送方式是否支持货到付款 */
    $modules[$i]['cod']     = TRUE;

    /* 插件的作者 */
    $modules[$i]['author']  = 'ECTouch Team';

    /* 插件作者的官方网站 */
    $modules[$i]['website'] = 'http://www.ectouch.cn';

    /* 配送接口需要的参数 */
    $modules[$i]['configure'] = array(
                                   array('name' => 'base_fee',     'value'=>10),
                                );

    /* 模式编辑器 */
    $modules[$i]['print_model'] = 2;

    /* 打印单背景 */
    $modules[$i]['print_bg'] = '';

   /* 打印快递单标签位置信息 */
    $modules[$i]['config_lable'] = '';

    return;
}

