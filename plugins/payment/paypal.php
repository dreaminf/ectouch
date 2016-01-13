<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：paypal.php
 * ----------------------------------------------------------------------------
 * 功能描述：Paypal支付插件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

/**
 * 类
 */
class paypal {
    var	$API_UserName = '';
    var	$API_Password = '';
    var	$API_Signature= '';
		var	$API_Endpoint = '';
		var	$PAYPAL_URL   = '';

		var	$version='65.1';
		var	$nvpHeader = "";

    public function __construct(){
      $payment = model('Payment')->get_payment('paypal');
      $this->API_UserName    = $payment['paypal_username'];
      $this->API_Password    = $payment['paypal_password'];
      $this->API_Signature    = $payment['paypal_signature'];
			if($payment['paypal_sandbox']==1){
          $this->API_Endpoint    = 'https://api-3t.paypal.com/nvp';
          $this->PAYPAL_URL    = 'https://www.paypal.com/cgi-bin/webscr&cmd=_express-checkout&useraction=commit&token=';
			}else{
          $this->API_Endpoint    = 'https://api-3t.sandbox.paypal.com/nvp';
          $this->PAYPAL_URL    = 'https://www.sandbox.paypal.com/cgi-bin/webscr&cmd=_express-checkout&useraction=commit&token=';
			}
    	$this->nvpHeader = "&VERSION=".urlencode($this->version)."&PWD=".urlencode($this->API_Password)."&USER=".urlencode($this->API_UserName)."&SIGNATURE=".urlencode($this->API_Signature);
    }

    /**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code($order, $payment)
    {
        $token = '';
        $serverName = $_SERVER['SERVER_NAME'];
        $serverPort = $_SERVER['SERVER_PORT'];
        $url=dirname('http://'.$serverName.':'.$serverPort.$_SERVER['REQUEST_URI']);
        $nvpstr = "";
        $paymentAmount=$order['order_amount'];
	      $currencyCodeType=$payment['paypal_currency']; //币种
        $paymentType='Sale';
        $data_order_id	= $order['log_id'];

        $nvpstr.="&PAYMENTREQUEST_0_AMT=".$paymentAmount;
        $nvpstr.="&PAYMENTREQUEST_0_PAYMENTACTION=".$paymentType;
        $nvpstr.="&PAYMENTREQUEST_0_CURRENCYCODE=".$currencyCodeType;
        $nvpstr.="&PAYMENTREQUEST_0_INVNUM=".$data_order_id;
        $nvpstr.="&ButtonSource=ECTouch";
        $nvpstr.="&NOSHIPPING=1";

        $returnURL =urlencode($url.'/respond.php?code=paypal');
        $cancelURL =urlencode($url.'/respond.php?code=paypal');
				$nvpstr.= "&ReturnUrl=".$returnURL ;
				$nvpstr.= "&CANCELURL=".$cancelURL ;
	      $nvpstr.= "&SolutionType=Sole";
	      $nvpstr.= "&LandingPage=Billing";
				$resArray=$this->hash_call("SetExpressCheckout",$nvpstr);

        $_SESSION['reshash']=$resArray;
        if(isset($resArray["ACK"])){
            $ack = strtoupper($resArray["ACK"]);
        }

        if (isset($resArray["TOKEN"])){
            $token = urldecode($resArray["TOKEN"]);
        }
        $payPalURL = $this->PAYPAL_URL.$token;
        $button = '<div style="text-align:center"><input type="submit" onclick="window.open(\''.$payPalURL. '\')" value="跳转到PAYPAL支付" class="btn btn-info ect-btn-info ect-colorf ect-bg" /></div>';
        return $button;
    }

    /**
     * 响应操作
     */
    public function callback($data)
    {
        return $this->notify();
    }
    
    /**
     * Paypal异步通知
     * 
     * @return string
     */
    public function notify($data)
    {
        $token = urlencode($_REQUEST["token"]);
        $nvpstr="&TOKEN=".$token;
        $resArray=$this->hash_call("GetExpressCheckoutDetails",$nvpstr);
        $_SESSION['reshash']=$resArray;
        $ack = strtoupper($resArray["ACK"]);

        if($ack=="SUCCESS"){
						$payerID = urlencode($resArray["PAYERID"]);
						$currCodeType	=	urlencode($resArray["PAYMENTREQUEST_0_CURRENCYCODE"]);
						$paymentType	=	urlencode($resArray["PAYMENTREQUEST_0_PAYMENTACTION"]);
						$paymentAmount= urlencode($resArray["PAYMENTREQUEST_0_AMT"]);
						$order_sn= urlencode($resArray["PAYMENTREQUEST_0_INVNUM"]);
	          $serverName = urlencode($_SERVER["SERVER_NAME"]);

						$nvpstr ="&TOKEN=".$token;
						$nvpstr.="&PAYERID=".$payerID;
						$nvpstr.="&PAYMENTREQUEST_0_PAYMENTACTION=".$paymentType;
						$nvpstr.="&PAYMENTREQUEST_0_AMT=".$paymentAmount;
						$nvpstr.="&PAYMENTREQUEST_0_CURRENCYCODE=".$currCodeType;
		        $nvpstr.="&PAYMENTREQUEST_0_INVNUM=".$order_sn;
						$nvpstr.="&IPADDRESS=".$serverName ;
        		$nvpstr.="&ButtonSource=";

            $resArray=$this->hash_call("DoExpressCheckoutPayment",$nvpstr);
            $ack = strtoupper($resArray["ACK"]);
						if($ack != "SUCCESS" && $ack != "SUCCESSWITHWARNING"){
                return false;
	          }else{
                model('Payment')->order_paid($order_sn, 2);
                return true;
            }
        }else{
            return false;
        }
    }
    
    private function hash_call($methodName,$nvpStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $nvpreq="METHOD=".urlencode($methodName).$this->nvpHeader.$nvpStr;

        curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);
        $response = curl_exec($ch);
        $nvpResArray=$this->deformatNVP($response);
        $nvpReqArray=$this->deformatNVP($nvpreq);
        $_SESSION['nvpReqArray']=$nvpReqArray;
        if (curl_errno($ch)){
            $_SESSION['curl_error_no']=curl_errno($ch) ;
            $_SESSION['curl_error_msg']=curl_error($ch);
        }else{
            curl_close($ch);
        }
        return $nvpResArray;
    }

		//格式化
    private function deformatNVP($nvpstr)
    {
        $intial=0;
        $nvpArray = array();

        while(strlen($nvpstr))
        {
            $keypos= strpos($nvpstr,'=');
            $valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
            $keyval=substr($nvpstr,$intial,$keypos);
            $valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
            $nvpArray[urldecode($keyval)] =urldecode( $valval);
            $nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
        }

        return $nvpArray;
    }
}
