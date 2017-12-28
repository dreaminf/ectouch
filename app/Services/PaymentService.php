<?php

namespace App\Services;

use App\Repositorys\ArticleRepository;

class PaymentService
{
    private $article;

    public function __construct(ArticleRepository $article)
    {
        $this->article = $article;
    }

    /**
     * @param $condition
     * @return mixed
     */
    public function all($condition = [])
    {
        return $this->article->all($condition);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function create($data)
    {

    }

    /**
     * @param $id
     * @return mixed
     */
    public function detail($id)
    {
        return $this->article->show($id);

    }

    /**
     * @param $data
     * @return mixed
     */
    public function update($data)
    {

    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {

    }

    /**
     * 取得已安装的支付方式列表
     * @return  array   已安装的配送方式列表
     */
    function payment_list()
    {
        $sql = 'SELECT pay_id, pay_name ' .
            'FROM ' . $GLOBALS['ecs']->table('payment') .
            ' WHERE enabled = 1';

        return $GLOBALS['db']->getAll($sql);
    }

    /**
     * 取得支付方式信息
     * @param   int $pay_id 支付方式id
     * @return  array   支付方式信息
     */
    function payment_info($pay_id)
    {
        $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('payment') .
            " WHERE pay_id = '$pay_id' AND enabled = 1";

        return $GLOBALS['db']->getRow($sql);
    }

    /**
     * 获得订单需要支付的支付费用
     *
     * @access  public
     * @param   integer $payment_id
     * @param   float $order_amount
     * @param   mix $cod_fee
     * @return  float
     */
    function pay_fee($payment_id, $order_amount, $cod_fee = null)
    {
        $pay_fee = 0;
        $payment = payment_info($payment_id);
        $rate = ($payment['is_cod'] && !is_null($cod_fee)) ? $cod_fee : $payment['pay_fee'];

        if (strpos($rate, '%') !== false) {
            // 支付费用是一个比例
            $val = floatval($rate) / 100;
            $pay_fee = $val > 0 ? $order_amount * $val / (1 - $val) : 0;
        } else {
            $pay_fee = floatval($rate);
        }

        return round($pay_fee, 2);
    }

    /**
     * 取得可用的支付方式列表
     * @param   bool $support_cod 配送方式是否支持货到付款
     * @param   int $cod_fee 货到付款手续费（当配送方式支持货到付款时才传此参数）
     * @param   int $is_online 是否支持在线支付
     * @return  array   配送方式数组
     */
    function available_payment_list($support_cod, $cod_fee = 0, $is_online = false)
    {
        $sql = 'SELECT pay_id, pay_code, pay_name, pay_fee, pay_desc, pay_config, is_cod' .
            ' FROM ' . $GLOBALS['ecs']->table('payment') .
            ' WHERE enabled = 1 ';
        if (!$support_cod) {
            $sql .= 'AND is_cod = 0 '; // 如果不支持货到付款
        }
        if ($is_online) {
            $sql .= "AND is_online = '1' ";
        }
        $sql .= 'ORDER BY pay_order'; // 排序
        $res = $GLOBALS['db']->query($sql);

        $pay_list = [];
        foreach ($res as $row) {
            if ($row['is_cod'] == '1') {
                $row['pay_fee'] = $cod_fee;
            }

            $row['format_pay_fee'] = strpos($row['pay_fee'], '%') !== false ? $row['pay_fee'] :
                price_format($row['pay_fee'], false);
            $modules[] = $row;
        }

        load_helper('compositor');

        if (isset($modules)) {
            return $modules;
        }
    }

}