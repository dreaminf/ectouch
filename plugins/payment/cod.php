<?php

/**
 * Class Cod
 * Desc: 货到付款插件
 * Author: carson
 * Email: wanganlin@ecmoban.com
 * Date: 20150608
 */

class Cod implements PaymentInterface
{

    /**
     * 生成支付代码
     * @param   array $order 订单信息
     * @param   array $payment 支付方式信息
     */
    public function get_code($order, $payment)
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

    /**
     * 同步通知
     * @param $data
     * @return mixed
     */
    public function callback($data)
    {

    }

    /**
     * 异步通知
     * @param $data
     * @return mixed
     */
    public function notify($data)
    {

    }

    /**
     * 订单查询
     * @return mixed
     */
    public function query($order, $payment)
    {

    }


}