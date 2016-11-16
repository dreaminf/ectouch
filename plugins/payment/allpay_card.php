<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：alipay_wap.php
 * ----------------------------------------------------------------------------
 * 功能描述：手机欧付宝支付插件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */

defined('IN_ECTOUCH') or die('Deny Access');


include_once(ROOT_PATH . '/include/modules/AllPay.Payment.Integration.php');
/**
 * 支付插件类-- 信用卡
 */
class allpay_card extends AllInOne {

    private $lang;
    /**
     * 構造函數
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function __construct() {
        parent::__construct();
        $this->allpay_alipay();
    }

    function allpay_alipay() {
        $payment_lang = BASE_PATH . 'languages/' . C('lang') . '/payment/allpay_card.php';

        if (file_exists($payment_lang)) {
            include_once($payment_lang);
            $this->lang = $_LANG;
        }
    }
    /**
     * 提交函數
     */
    function get_code($order, $payment) {
        $isTestMode = ($payment['allpay_card_test_mode'] == 'Yes');

        $this->ServiceURL = ($isTestMode ? "http://payment-stage.allpay.com.tw/Cashier/AioCheckOut" : "https://payment.allpay.com.tw/Cashier/AioCheckOut");
        $this->HashKey = trim($payment['allpay_card_key']);
        $this->HashIV = trim($payment['allpay_card_iv']);
        $this->MerchantID = trim($payment['allpay_card_account']);

        $szRetUrl = return_url(basename(__FILE__, '.php'))."&log_id=".$order['log_id'];
        $szRetUrl = str_ireplace('/mobile/', '/', $szRetUrl);

        $this->Send['ReturnURL'] = return_url(basename(__FILE__, '.php'), true);
        $this->Send['ClientBackURL'] = return_url(basename(__FILE__, '.php'));

        $this->Send['OrderResultURL'] = $szRetUrl;
        $this->Send['MerchantTradeNo'] = $order['order_sn'];
        $this->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');
        $this->Send['TotalAmount'] = (int)$order['order_amount'];
        $this->Send['TradeDesc'] = "AllPay_ECShop_Module";
        $this->Send['ChoosePayment'] = PaymentMethod::Credit;
        $this->Send['Remark'] = '';
        $this->Send['ChooseSubPayment'] = PaymentMethodItem::None;
        $this->Send['NeedExtraPaidInfo'] = ExtraPaymentInfo::No;
        $this->Send['DeviceSource'] = DeviceType::PC;

        array_push($this->Send['Items'], array('Name' => $this->lang['text_goods'], 'Price' => intval($order['order_amount']), 'Currency' => $this->lang['text_currency'], 'Quantity' => 1, 'URL' => ''));

        return $this->CheckOutString($this->lang['pay_button']);
    }
    /**
     * 手机同步响应操作
     *
     * @return boolean
     */
    public function callback($data)
    {
        return true;
    }

    /**
     * 處理函數
     */
    function notify($data) {
        $arPayment = model('Payment')->get_payment('allpay_card');

        $isTestMode = ($arPayment['allpay_card_test_mode'] == 'Yes');

        $arFeedback = null;
        $arQueryFeedback = null;

        $this->HashKey = trim($arPayment['allpay_card_key']);
        $this->HashIV = trim($arPayment['allpay_card_iv']);
        try {
            // 取得回傳的付款結果。
            $arFeedback = $this->CheckOutFeedback();

            if (sizeof($arFeedback) > 0) {
                // 查詢付款結果資料。
                $this->ServiceURL = ($isTestMode ? "http://payment-stage.allpay.com.tw/Cashier/QueryTradeInfo" : "https://payment.allpay.com.tw/Cashier/QueryTradeInfo");
                $this->MerchantID = trim($arPayment['allpay_card_account']);

                $this->Query['MerchantTradeNo'] = $arFeedback['MerchantTradeNo'];
                $arQueryFeedback = $this->QueryTradeInfo();

                if (sizeof($arQueryFeedback) > 0) {
                    // 檢查支付金額與訂單是否相符。
                    $szLogID = model('Payment')->get_log_id($arFeedback['MerchantTradeNo']); // 订单号log_id
                    if (model('Payment')->check_money($szLogID, $arFeedback['TradeAmt']) && $arQueryFeedback['TradeAmt'] == $arFeedback['TradeAmt']) {
                        $szCheckAmount = '1';
                    }
                    // 確認付款結果。

                    if ($arFeedback['RtnCode'] == '1' && $szCheckAmount == '1' && $arQueryFeedback["TradeStatus"] == '1') {
                        $szNote = $this->lang['text_paid'] . date("Y-m-d H:i:s");
                        model('Payment')->order_paid($szLogID, PS_PAYED, $szNote);

                        if ($_GET['background']){
                            echo '1|OK';
                            exit;
                        } else {
                            return true;
                        }
                    } else {
                        if ($_GET['background']){
                            echo (!$szCheckAmount ? '0|訂單金額不符。' : $arFeedback['RtnMsg']);
                            exit;
                        } else {
                            return false;
                        }
                    }
                } else {
                    throw new Exception('AllPay 查無訂單資料。');
                }
            }
        } catch (Exception $ex) { /* 例外處理 */
        }

        return false;
    }

}
