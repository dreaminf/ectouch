<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$shipping_lang = ROOT_PATH.'include/language/' . C('lang') . '/shipping/ems.php';
if (file_exists($shipping_lang))
{
    global $_LANG;
    include_once($shipping_lang);
}

include_once(ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/admin/shipping.php');

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = (isset($modules)) ? count($modules) : 0;

    /* 配送方式插件的代码必须和文件名保持一致 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    $modules[$i]['version'] = '1.0.0';

    /* 配送方式的描述 */
    $modules[$i]['desc']    = 'ems_express_desc';

    /* 配送方式是否支持货到付款 */
    $modules[$i]['cod']     = false;

    /* 插件的作者 */
    $modules[$i]['author']  = 'ECTouch Team';

    /* 插件作者的官方网站 */
    $modules[$i]['website'] = 'http://www.ectouch.cn';

    /* 配送接口需要的参数 */
    $modules[$i]['configure'] = array(
                                    array('name' => 'item_fee',     'value'=>20),
                                    array('name' => 'base_fee',     'value'=>20),
                                    array('name' => 'step_fee',     'value'=>15),
                                );

    /* 模式编辑器 */
    $modules[$i]['print_model'] = 2;

    /* 打印单背景 */
    $modules[$i]['print_bg'] = '/images/receipt/dly_ems.jpg';

   /* 打印快递单标签位置信息 */
    $modules[$i]['config_lable'] = 't_shop_name,' . $_LANG['lable_box']['shop_name'] . ',236,32,182,161,b_shop_name||,||t_shop_tel,' . $_LANG['lable_box']['shop_tel'] . ',127,21,295,135,b_shop_tel||,||t_shop_address,' . $_LANG['lable_box']['shop_address'] . ',296,68,124,190,b_shop_address||,||t_pigeon,' . $_LANG['lable_box']['pigeon'] . ',21,21,192,278,b_pigeon||,||t_customer_name,' . $_LANG['lable_box']['customer_name'] . ',107,23,494,136,b_customer_name||,||t_customer_tel,' . $_LANG['lable_box']['customer_tel'] . ',155,21,639,124,b_customer_tel||,||t_customer_mobel,' . $_LANG['lable_box']['customer_mobel'] . ',159,21,639,147,b_customer_mobel||,||t_customer_post,' . $_LANG['lable_box']['customer_post'] . ',88,21,680,258,b_customer_post||,||t_year,' . $_LANG['lable_box']['year'] . ',37,21,534,379,b_year||,||t_months,' . $_LANG['lable_box']['months'] . ',29,21,592,379,b_months||,||t_day,' . $_LANG['lable_box']['day'] . ',27,21,642,380,b_day||,||t_order_best_time,' . $_LANG['lable_box']['order_best_time'] . ',104,39,688,359,b_order_best_time||,||t_order_postscript,' . $_LANG['lable_box']['order_postscript'] . ',305,34,485,402,b_order_postscript||,||t_customer_address,' . $_LANG['lable_box']['customer_address'] . ',289,48,503,190,b_customer_address||,||';

    return;
}

