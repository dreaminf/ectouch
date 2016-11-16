<?php
defined('IN_ECTOUCH') or die('Deny Access');

$payment_lang = BASE_PATH . 'languages/' . C('lang') . '/payment/allpay_card.php';

if (file_exists($payment_lang)) {
    global $_LANG;
    include_once($payment_lang);
}

/* 模塊的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;
    /* 代碼 */
    $modules[$i]['code'] = basename(__FILE__, '.php');
    /* 描述對應的語言項 */
    $modules[$i]['desc'] = 'allpay_card_desc';
    /* 是否支持貨到付款 */
    $modules[$i]['is_cod'] = '0';
    /* 是否支持在線支付 */
    $modules[$i]['is_online'] = '1';
    /* 排序 */
    //$modules[$i]['pay_order']  = '1';
    /* 作者 */
    $modules[$i]['author'] = '歐付寶';
    /* 網址 */
    $modules[$i]['website'] = 'http://www.allpay.com.tw';
    /* 版本號 */
    $modules[$i]['version'] = 'V0.1';
    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('name' => 'allpay_card_test_mode', 'type' => 'select', 'value' => 'Yes'),
        array('name' => 'allpay_card_account', 'type' => 'text', 'value' => '1111'),
        array('name' => 'allpay_card_iv', 'type' => 'text', 'value' => 'iv'),
        array('name' => 'allpay_card_key', 'type' => 'text', 'value' => 'key')
    );
    return;
}


