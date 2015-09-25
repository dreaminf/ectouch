<?php

/**
 * Interface ShoppingInterface
 * Desc: 配送方式接口
 * Author: carson
 * Email: wanganlin@ecmoban.com
 * Date: 20150608
 */
interface ShippingInterface
{

    /**
     * 计算订单的配送费用的函数
     *
     * @param   float $goods_weight 商品重量
     * @param   float $goods_amount 商品金额
     * @param   float $goods_number 商品数量
     * @return  decimal
     */
    public function calculate($goods_weight, $goods_amount, $goods_number);

    /**
     * 查询快递状态
     *
     * @access  public
     * @return  string  查询窗口的链接地址
     */
    public function query($invoice_sn);
}