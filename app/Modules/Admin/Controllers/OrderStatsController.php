<?php

namespace App\Modules\Admin\Controllers;

/**
 * 订单统计
 * Class OrderStatsController
 * @package App\Modules\Admin\Controllers
 */
class OrderStatsController extends BaseController
{
    public function actionIndex()
    {
        load_helper('order');
        load_lang('statistic', 'admin');

        $this->smarty->assign('lang', $GLOBALS['_LANG']);

        /**
         *订单统计
         */
        if ($_REQUEST['act'] == 'list') {
            admin_priv('sale_order_stats');

            // 随机的颜色数组 
            $color_array = ['33FF66', 'FF6600', '3399FF', '009966', 'CC3399', 'FFCC33', '6699CC', 'CC3366'];

            // 计算订单各种费用之和的语句 
            $total_fee = " SUM(" . order_amount_field() . ") AS total_turnover ";

            // 取得订单转化率数据 
            $sql = "SELECT COUNT(*) AS total_order_num, " . $total_fee .
                " FROM " . $this->ecs->table('order_info') .
                " WHERE 1 " . order_query_sql('finished');
            $order_general = $this->db->getRow($sql);
            $order_general['total_turnover'] = floatval($order_general['total_turnover']);

            // 取得商品总点击数量 
            $sql = 'SELECT SUM(click_count) FROM ' . $this->ecs->table('goods') . ' WHERE is_delete = 0';
            $click_count = floatval($this->db->getOne($sql));

            // 每千个点击的订单数 
            $click_ordernum = $click_count > 0 ? round(($order_general['total_order_num'] * 1000) / $click_count, 2) : 0;

            // 每千个点击的购物额 
            $click_turnover = $click_count > 0 ? round(($order_general['total_turnover'] * 1000) / $click_count, 2) : 0;

            // 时区 
            $timezone = session('?timezone') ? session('timezone') : $GLOBALS['_CFG']['timezone'];

            // 时间参数 
            $is_multi = empty($_POST['is_multi']) ? false : true;

            // 时间参数 
            if (isset($_POST['start_date']) && !empty($_POST['end_date'])) {
                $start_date = local_strtotime($_POST['start_date']);
                $end_date = local_strtotime($_POST['end_date']);
                if ($start_date == $end_date) {
                    $end_date = $start_date + 86400;
                }
            } else {
                $today = strtotime(local_date('Y-m-d'));   //本地时间
                $start_date = $today - 86400 * 6;
                $end_date = $today + 86400;               //至明天零时
            }

            $start_date_arr = [];
            $end_date_arr = [];
            if (!empty($_POST['year_month'])) {
                $tmp = $_POST['year_month'];

                for ($i = 0; $i < count($tmp); $i++) {
                    if (!empty($tmp[$i])) {
                        $tmp_time = local_strtotime($tmp[$i] . '-1');
                        $start_date_arr[] = $tmp_time;
                        $end_date_arr[] = local_strtotime($tmp[$i] . '-' . date('t', $tmp_time));
                    }
                }
            } else {
                $tmp_time = local_strtotime(local_date('Y-m-d'));
                $start_date_arr[] = local_strtotime(local_date('Y-m') . '-1');
                $end_date_arr[] = local_strtotime(local_date('Y-m') . '-31');;
            }

            // 按月份交叉查询 
            if ($is_multi) {
                // 订单概况 
                $order_general_xml = "<chart caption='{$GLOBALS['_LANG'][order_circs]}' shownames='1' showvalues='0' decimals='0' outCnvBaseFontSize='12' baseFontSize='12' >";
                $order_general_xml .= "<categories><category label='{$GLOBALS['_LANG'][confirmed]}' />" .
                    "<category label='{$GLOBALS['_LANG'][succeed]}' />" .
                    "<category label='{$GLOBALS['_LANG'][unconfirmed]}' />" .
                    "<category label='{$GLOBALS['_LANG'][invalid]}' /></categories>";
                foreach ($start_date_arr AS $k => $val) {
                    $seriesName = local_date('Y-m', $val);
                    $order_info = $this->get_orderinfo($start_date_arr[$k], $end_date_arr[$k]);
                    $order_general_xml .= "<dataset seriesName='$seriesName' color='$color_array[$k]' showValues='0'>";
                    $order_general_xml .= "<set value='$order_info[confirmed_num]' />";
                    $order_general_xml .= "<set value='$order_info[succeed_num]' />";
                    $order_general_xml .= "<set value='$order_info[unconfirmed_num]' />";
                    $order_general_xml .= "<set value='$order_info[invalid_num]' />";
                    $order_general_xml .= "</dataset>";
                }
                $order_general_xml .= "</chart>";

                // 支付方式 
                $pay_xml = "<chart caption='{$GLOBALS['_LANG'][pay_method]}' shownames='1' showvalues='0' decimals='0' outCnvBaseFontSize='12' baseFontSize='12' >";

                $payment = [];
                $payment_count = [];

                foreach ($start_date_arr AS $k => $val) {
                    $sql = 'SELECT i.pay_id, p.pay_name, i.pay_time, COUNT(i.order_id) AS order_num ' .
                        'FROM ' . $this->ecs->table('payment') . ' AS p, ' . $this->ecs->table('order_info') . ' AS i ' .
                        "WHERE p.pay_id = i.pay_id AND i.order_status = '" . OS_CONFIRMED . "' " .
                        "AND i.pay_status > '" . PS_UNPAYED . "' AND i.shipping_status > '" . SS_UNSHIPPED . "' " .
                        "AND i.add_time >= '$start_date_arr[$k]' AND i.add_time <= '$end_date_arr[$k]'" .
                        "GROUP BY i.pay_id ORDER BY order_num DESC";
                    $pay_res = $this->db->query($sql);
                    foreach ($pay_res as $pay_item) {
                        $payment[$pay_item['pay_name']] = null;

                        $paydate = local_date('Y-m', $pay_item['pay_time']);

                        $payment_count[$pay_item['pay_name']][$paydate] = $pay_item['order_num'];
                    }
                }

                $pay_xml .= "<categories>";
                foreach ($payment AS $k => $val) {
                    $pay_xml .= "<category label='$k' />";
                }
                $pay_xml .= "</categories>";

                foreach ($start_date_arr AS $k => $val) {
                    $date = local_date('Y-m', $start_date_arr[$k]);
                    $pay_xml .= "<dataset seriesName='$date' color='$color_array[$k]' showValues='0'>";
                    foreach ($payment AS $k => $val) {
                        $count = 0;
                        if (!empty($payment_count[$k][$date])) {
                            $count = $payment_count[$k][$date];
                        }

                        $pay_xml .= "<set value='$count' name='$date' />";
                    }
                    $pay_xml .= "</dataset>";
                }
                $pay_xml .= "</chart>";

                // 配送方式 
                $ship = [];
                $ship_count = [];

                $ship_xml = "<chart caption='{$GLOBALS['_LANG'][shipping_method]}' shownames='1' showvalues='0' decimals='0' outCnvBaseFontSize='12' baseFontSize='12' >";

                foreach ($start_date_arr AS $k => $val) {
                    $sql = 'SELECT sp.shipping_id, sp.shipping_name AS ship_name, i.shipping_time, COUNT(i.order_id) AS order_num ' .
                        'FROM ' . $this->ecs->table('shipping') . ' AS sp, ' . $this->ecs->table('order_info') . ' AS i ' .
                        'WHERE sp.shipping_id = i.shipping_id ' . order_query_sql('finished') .
                        "AND i.add_time >= '$start_date_arr[$k]' AND i.add_time <= '$end_date_arr[$k]' " .
                        "GROUP BY i.shipping_id ORDER BY order_num DESC";

                    $ship_res = $this->db->query($sql);
                    foreach ($ship_res as $ship_item) {
                        $ship[$ship_item['ship_name']] = null;

                        $shipdate = local_date('Y-m', $ship_item['shipping_time']);

                        $ship_count[$ship_item['ship_name']][$shipdate] = $ship_item['order_num'];
                    }
                }

                $ship_xml .= "<categories>";
                foreach ($ship AS $k => $val) {
                    $ship_xml .= "<category label='$k' />";
                }
                $ship_xml .= "</categories>";

                foreach ($start_date_arr AS $k => $val) {
                    $date = local_date('Y-m', $start_date_arr[$k]);

                    $ship_xml .= "<dataset seriesName='$date' color='$color_array[$k]' showValues='0'>";
                    foreach ($ship AS $k => $val) {
                        $count = 0;
                        if (!empty($ship_count[$k][$date])) {
                            $count = $ship_count[$k][$date];
                        }
                        $ship_xml .= "<set value='$count' name='$date' />";
                    }
                    $ship_xml .= "</dataset>";
                }
                $ship_xml .= "</chart>";
            } // 按时间段查询 
            else {
                // 订单概况 
                $order_info = $this->get_orderinfo($start_date, $end_date);

                $order_general_xml = "<graph caption='" . $GLOBALS['_LANG']['order_circs'] . "' decimalPrecision='2' showPercentageValues='0' showNames='1' showValues='1' showPercentageInLabel='0' pieYScale='45' pieBorderAlpha='40' pieFillAlpha='70' pieSliceDepth='15' pieRadius='100' outCnvBaseFontSize='13' baseFontSize='12'>";

                $order_general_xml .= "<set value='" . $order_info['confirmed_num'] . "' name='" . $GLOBALS['_LANG']['confirmed'] . "' color='" . $color_array[5] . "' />";

                $order_general_xml .= "<set value='" . $order_info['succeed_num'] . "' name='" . $GLOBALS['_LANG']['succeed'] . "' color='" . $color_array[0] . "' />";

                $order_general_xml .= "<set value='" . $order_info['unconfirmed_num'] . "' name='" . $GLOBALS['_LANG']['unconfirmed'] . "' color='" . $color_array[1] . "'  />";

                $order_general_xml .= "<set value='" . $order_info['invalid_num'] . "' name='" . $GLOBALS['_LANG']['invalid'] . "' color='" . $color_array[4] . "' />";
                $order_general_xml .= "</graph>";

                // 支付方式 
                $pay_xml = "<graph caption='" . $GLOBALS['_LANG']['pay_method'] . "' decimalPrecision='2' showPercentageValues='0' showNames='1' numberPrefix='' showValues='1' showPercentageInLabel='0' pieYScale='45' pieBorderAlpha='40' pieFillAlpha='70' pieSliceDepth='15' pieRadius='100' outCnvBaseFontSize='13' baseFontSize='12'>";

                $sql = 'SELECT i.pay_id, p.pay_name, COUNT(i.order_id) AS order_num ' .
                    'FROM ' . $this->ecs->table('payment') . ' AS p, ' . $this->ecs->table('order_info') . ' AS i ' .
                    "WHERE p.pay_id = i.pay_id " . order_query_sql('finished') .
                    "AND i.add_time >= '$start_date' AND i.add_time <= '$end_date' " .
                    "GROUP BY i.pay_id ORDER BY order_num DESC";
                $pay_res = $this->db->query($sql);

                foreach ($pay_res as $pay_item) {
                    $pay_xml .= "<set value='" . $pay_item['order_num'] . "' name='" . $pay_item['pay_name'] . "' color='" . $color_array[mt_rand(0, 7)] . "'/>";
                }
                $pay_xml .= "</graph>";

                // 配送方式 
                $ship_xml = "<graph caption='" . $GLOBALS['_LANG']['shipping_method'] . "' decimalPrecision='2' showPercentageValues='0' showNames='1' numberPrefix='' showValues='1' showPercentageInLabel='0' pieYScale='45' pieBorderAlpha='40' pieFillAlpha='70' pieSliceDepth='15' pieRadius='100' outCnvBaseFontSize='13' baseFontSize='12'>";

                $sql = 'SELECT sp.shipping_id, sp.shipping_name AS ship_name, COUNT(i.order_id) AS order_num ' .
                    'FROM ' . $this->ecs->table('shipping') . ' AS sp, ' . $this->ecs->table('order_info') . ' AS i ' .
                    'WHERE sp.shipping_id = i.shipping_id ' . order_query_sql('finished') .
                    "AND i.add_time >= '$start_date' AND i.add_time <= '$end_date' " .
                    "GROUP BY i.shipping_id ORDER BY order_num DESC";
                $ship_res = $this->db->query($sql);

                foreach ($ship_res as $ship_item) {
                    $ship_xml .= "<set value='" . $ship_item['order_num'] . "' name='" . $ship_item['ship_name'] . "' color='" . $color_array[mt_rand(0, 7)] . "' />";
                }

                $ship_xml .= "</graph>";

            }
            // 赋值到模板 
            $this->smarty->assign('order_general', $order_general);
            $this->smarty->assign('total_turnover', price_format($order_general['total_turnover']));
            $this->smarty->assign('click_count', $click_count);         //商品总点击数
            $this->smarty->assign('click_ordernum', $click_ordernum);      //每千点订单数
            $this->smarty->assign('click_turnover', price_format($click_turnover));  //每千点购物额

            $this->smarty->assign('is_multi', $is_multi);

            $this->smarty->assign('order_general_xml', $order_general_xml);
            $this->smarty->assign('ship_xml', $ship_xml);
            $this->smarty->assign('pay_xml', $pay_xml);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['report_order']);
            $this->smarty->assign('start_date', local_date($GLOBALS['_CFG']['date_format'], $start_date));
            $this->smarty->assign('end_date', local_date($GLOBALS['_CFG']['date_format'], $end_date));

            for ($i = 0; $i < 5; $i++) {
                if (isset($start_date_arr[$i])) {
                    $start_date_arr[$i] = local_date('Y-m', $start_date_arr[$i]);
                } else {
                    $start_date_arr[$i] = null;
                }
            }
            $this->smarty->assign('start_date_arr', $start_date_arr);

            if (!$is_multi) {
                $filename = local_date('Ymd', $start_date) . '_' . local_date('Ymd', $end_date);
                $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['down_order_statistics'], 'href' => 'order_stats.php?act=download&start_date=' . $start_date . '&end_date=' . $end_date . '&filename=' . $filename]);
            }


            return $this->smarty->display('order_stats.htm');
        } elseif ($act = 'download') {
            $filename = !empty($_REQUEST['filename']) ? trim($_REQUEST['filename']) : '';

            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=$filename.xls");
            $start_date = empty($_REQUEST['start_date']) ? strtotime('-20 day') : intval($_REQUEST['start_date']);
            $end_date = empty($_REQUEST['end_date']) ? time() : intval($_REQUEST['end_date']);
            // 订单概况 
            $order_info = $this->get_orderinfo($start_date, $end_date);
            $data = $GLOBALS['_LANG']['order_circs'] . "\n";
            $data .= "{$GLOBALS['_LANG'][confirmed]} \t {$GLOBALS['_LANG'][succeed]} \t {$GLOBALS['_LANG'][unconfirmed]} \t {$GLOBALS['_LANG'][invalid]} \n";
            $data .= "$order_info[confirmed_num] \t $order_info[succeed_num] \t $order_info[unconfirmed_num] \t $order_info[invalid_num]\n";
            $data .= "\n{$GLOBALS['_LANG'][pay_method]}\n";

            // 支付方式 
            $sql = 'SELECT i.pay_id, p.pay_name, COUNT(i.order_id) AS order_num ' .
                'FROM ' . $this->ecs->table('payment') . ' AS p, ' . $this->ecs->table('order_info') . ' AS i ' .
                "WHERE p.pay_id = i.pay_id " . order_query_sql('finished') .
                "AND i.add_time >= '$start_date' AND i.add_time <= '$end_date' " .
                "GROUP BY i.pay_id ORDER BY order_num DESC";
            $pay_res = $this->db->getAll($sql);
            foreach ($pay_res AS $val) {
                $data .= $val['pay_name'] . "\t";
            }
            $data .= "\n";
            foreach ($pay_res AS $val) {
                $data .= $val['order_num'] . "\t";
            }

            // 配送方式 
            $sql = 'SELECT sp.shipping_id, sp.shipping_name AS ship_name, COUNT(i.order_id) AS order_num ' .
                'FROM ' . $this->ecs->table('shipping') . ' AS sp, ' . $this->ecs->table('order_info') . ' AS i ' .
                'WHERE sp.shipping_id = i.shipping_id ' . order_query_sql('finished') .
                "AND i.add_time >= '$start_date' AND i.add_time <= '$end_date' " .
                "GROUP BY i.shipping_id ORDER BY order_num DESC";
            $ship_res = $this->db->getAll($sql);

            $data .= "\n{$GLOBALS['_LANG'][shipping_method]}\n";
            foreach ($ship_res AS $val) {
                $data .= $val['ship_name'] . "\t";
            }
            $data .= "\n";
            foreach ($ship_res AS $val) {
                $data .= $val['order_num'] . "\t";
            }

            echo ecs_iconv(CHARSET, 'GB2312', $data) . "\t";
            exit;

        }
    }

    /**
     *订单统计需要的函数
     */
    /**
     * 取得订单概况数据(包括订单的几种状态)
     * @param       $start_date    开始查询的日期
     * @param       $end_date      查询的结束日期
     * @return      $order_info    订单概况数据
     */
    private function get_orderinfo($start_date, $end_date)
    {
        $order_info = [];

        // 未确认订单数 
        $sql = 'SELECT COUNT(*) AS unconfirmed_num FROM ' . $GLOBALS['ecs']->table('order_info') .
            " WHERE order_status = '" . OS_UNCONFIRMED . "' AND add_time >= '$start_date'" .
            " AND add_time < '" . ($end_date + 86400) . "'";

        $order_info['unconfirmed_num'] = $GLOBALS['db']->getOne($sql);

        // 已确认订单数 
        $sql = 'SELECT COUNT(*) AS confirmed_num FROM ' . $GLOBALS['ecs']->table('order_info') .
            " WHERE order_status = '" . OS_CONFIRMED . "' AND shipping_status NOT " . db_create_in([SS_SHIPPED, SS_RECEIVED]) . " AND pay_status NOT" . db_create_in([PS_PAYED, PS_PAYING]) . " AND add_time >= '$start_date'" .
            " AND add_time < '" . ($end_date + 86400) . "'";
        $order_info['confirmed_num'] = $GLOBALS['db']->getOne($sql);

        // 已成交订单数 
        $sql = 'SELECT COUNT(*) AS succeed_num FROM ' . $GLOBALS['ecs']->table('order_info') .
            " WHERE 1 " . order_query_sql('finished') .
            " AND add_time >= '$start_date' AND add_time < '" . ($end_date + 86400) . "'";
        $order_info['succeed_num'] = $GLOBALS['db']->getOne($sql);

        // 无效或已取消订单数 
        $sql = "SELECT COUNT(*) AS invalid_num FROM " . $GLOBALS['ecs']->table('order_info') .
            " WHERE order_status > '" . OS_CONFIRMED . "'" .
            " AND add_time >= '$start_date' AND add_time < '" . ($end_date + 86400) . "'";
        $order_info['invalid_num'] = $GLOBALS['db']->getOne($sql);
        return $order_info;
    }

}