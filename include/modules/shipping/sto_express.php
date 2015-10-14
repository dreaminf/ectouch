<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$shipping_lang = ROOT_PATH.'include/language/' . C('lang') . '/shipping/sto_express.php';
if (file_exists($shipping_lang))
{
    global $_LANG;
    include_once($shipping_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    include_once(ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/admin/shipping.php');

    $i = (isset($modules)) ? count($modules) : 0;

    /* 配送方式插件的代码必须和文件名保持一致 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    $modules[$i]['version'] = '1.0.0';

    /* 配送方式的描述 */
    $modules[$i]['desc']    = 'sto_express_desc';

    /* 配送方式是否支持货到付款 */
    $modules[$i]['cod']     = false;

    /* 插件的作者 */
    $modules[$i]['author']  = 'ECTouch Team';

    /* 插件作者的官方网站 */
    $modules[$i]['website'] = 'http://www.ectouch.cn';

    /* 配送接口需要的参数 */
    $modules[$i]['configure'] = array(
                                    array('name' => 'item_fee',     'value'=>15), /* 单件商品的配送费用 */
                                    array('name' => 'base_fee',    'value'=>15), /* 1000克以内的价格           */
                                    array('name' => 'step_fee',     'value'=>5),  /* 续重每1000克增加的价格 */
                                );

    /* 模式编辑器 */
    $modules[$i]['print_model'] = 2;

    /* 打印单背景 */
    $modules[$i]['print_bg'] = '/images/receipt/dly_sto_express.jpg';

   /* 打印快递单标签位置信息 */
    $modules[$i]['config_lable'] = 't_shop_address,' . $_LANG['lable_box']['shop_address'] . ',235,48,131,152,b_shop_address||,||t_shop_name,' . $_LANG['lable_box']['shop_name'] . ',237,26,131,200,b_shop_name||,||t_shop_tel,' . $_LANG['lable_box']['shop_tel'] . ',96,36,144,257,b_shop_tel||,||t_customer_post,' . $_LANG['lable_box']['customer_post'] . ',86,23,578,268,b_customer_post||,||t_customer_address,' . $_LANG['lable_box']['customer_address'] . ',232,49,434,149,b_customer_address||,||t_customer_name,' . $_LANG['lable_box']['customer_name'] . ',151,27,449,231,b_customer_name||,||t_customer_tel,' . $_LANG['lable_box']['customer_tel'] . ',90,32,452,261,b_customer_tel||,||';

    return;
}
