<?php
defined('IN_ECTOUCH') or die('Deny Access');

$payment_lang = BASE_PATH . 'languages/' .C('lang'). '/payment/paypal.php';

if (file_exists($payment_lang)) {
    global $_LANG;
    include_once($payment_lang);
}

if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    $modules[$i]['code']    = basename(__FILE__, '.php');
    $modules[$i]['desc']    = 'paypal_desc';
    $modules[$i]['is_cod']  = '0';
    $modules[$i]['is_online']  = '1';
    $modules[$i]['author']  = 'ECTouch';
    $modules[$i]['website'] = 'http://www.paypal.com';
    $modules[$i]['version'] = '2.0.0';
    $modules[$i]['config'] = array(
        array('name' => 'paypal_account', 'type' => 'text', 'value' => ''),
        array('name' => 'paypal_username', 'type' => 'text', 'value' => ''),
        array('name' => 'paypal_password', 'type' => 'text', 'value' => ''),
        array('name' => 'paypal_signature', 'type' => 'text', 'value' => ''),
        array('name' => 'paypal_sandbox', 'type' => 'select', 'value' => '1'),
        array('name' => 'paypal_currency', 'type' => 'select', 'value' => 'USD')
    );

    return;
}
