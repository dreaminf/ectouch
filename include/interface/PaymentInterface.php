<?php

/**
 * Interface PaymentInterface
 * Desc: 支付方式接口
 * Author: carson
 * Email: wanganlin@ecmoban.com
 * Date: 20150608
 */
interface PaymentInterface
{

    /**
     * 生成支付代码
     * @param   array $order 订单信息
     * @param   array $payment 支付方式信息
     */
    public function get_code($order, $payment);

    /**
     * 同步通知
     * @param $data
     * @return mixed
     */
    public function callback($data);

    /**
     * 异步通知
     * @param $data
     * @return mixed
     */
    public function notify($data);

    /**
     * 订单查询
     * @return mixed
     */
    public function query($order, $payment);
}