<?php

namespace App\Services;


class BounsService
{


    /**
     * 查询会员的红包金额
     *
     * @access  public
     * @param   integer $user_id
     * @return  void
     */
    function get_user_bonus($user_id = 0)
    {
        if ($user_id == 0) {
            $user_id = session('user_id');
        }

        $sql = "SELECT SUM(bt.type_money) AS bonus_value, COUNT(*) AS bonus_count " .
            "FROM " . $GLOBALS['ecs']->table('user_bonus') . " AS ub, " .
            $GLOBALS['ecs']->table('bonus_type') . " AS bt " .
            "WHERE ub.user_id = '$user_id' AND ub.bonus_type_id = bt.type_id AND ub.order_id = 0";
        $row = $GLOBALS['db']->getRow($sql);

        return $row;
    }

    /**
     *
     * @access  public
     * @param   int $user_id 用户ID
     * @param   int $num 列表显示条数
     * @param   int $start 显示起始位置
     *
     * @return  array       $arr             红保列表
     */
    function get_user_bouns_list($user_id, $num = 10, $start = 0)
    {
        $sql = "SELECT u.bonus_sn, u.order_id, b.type_name, b.type_money, b.min_goods_amount, b.use_start_date, b.use_end_date " .
            " FROM " . $GLOBALS['ecs']->table('user_bonus') . " AS u ," .
            $GLOBALS['ecs']->table('bonus_type') . " AS b" .
            " WHERE u.bonus_type_id = b.type_id AND u.user_id = '" . $user_id . "'";
        $res = $GLOBALS['db']->selectLimit($sql, $num, $start);
        $arr = [];

        $day = getdate();
        $cur_date = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        foreach ($res as $row) {
            // 先判断是否被使用，然后判断是否开始或过期
            if (empty($row['order_id'])) {
                // 没有被使用
                if ($row['use_start_date'] > $cur_date) {
                    $row['status'] = $GLOBALS['_LANG']['not_start'];
                } elseif ($row['use_end_date'] < $cur_date) {
                    $row['status'] = $GLOBALS['_LANG']['overdue'];
                } else {
                    $row['status'] = $GLOBALS['_LANG']['not_use'];
                }
            } else {
                $row['status'] = '<a href="user.php?act=order_detail&order_id=' . $row['order_id'] . '" >' . $GLOBALS['_LANG']['had_use'] . '</a>';
            }

            $row['use_startdate'] = local_date($GLOBALS['_CFG']['date_format'], $row['use_start_date']);
            $row['use_enddate'] = local_date($GLOBALS['_CFG']['date_format'], $row['use_end_date']);

            $arr[] = $row;
        }
        return $arr;
    }

    /**
     *  给指定用户添加一个指定红包
     *
     * @access  public
     * @param   int $user_id 用户ID
     * @param   string $bouns_sn 红包序列号
     *
     * @return  boolen      $result
     */
    function add_bonus($user_id, $bouns_sn)
    {
        if (empty($user_id)) {
            $GLOBALS['err']->add($GLOBALS['_LANG']['not_login']);

            return false;
        }

        // 查询红包序列号是否已经存在
        $sql = "SELECT bonus_id, bonus_sn, user_id, bonus_type_id FROM " . $GLOBALS['ecs']->table('user_bonus') .
            " WHERE bonus_sn = '$bouns_sn'";
        $row = $GLOBALS['db']->getRow($sql);
        if ($row) {
            if ($row['user_id'] == 0) {
                //红包没有被使用
                $sql = "SELECT send_end_date, use_end_date " .
                    " FROM " . $GLOBALS['ecs']->table('bonus_type') .
                    " WHERE type_id = '" . $row['bonus_type_id'] . "'";

                $bonus_time = $GLOBALS['db']->getRow($sql);

                $now = gmtime();
                if ($now > $bonus_time['use_end_date']) {
                    $GLOBALS['err']->add($GLOBALS['_LANG']['bonus_use_expire']);
                    return false;
                }

                $sql = "UPDATE " . $GLOBALS['ecs']->table('user_bonus') . " SET user_id = '$user_id' " .
                    "WHERE bonus_id = '$row[bonus_id]'";
                $result = $GLOBALS['db']->query($sql);
                if ($result) {
                    return true;
                } else {
                    return $GLOBALS['db']->errorMsg();
                }
            } else {
                if ($row['user_id'] == $user_id) {
                    //红包已经添加过了。
                    $GLOBALS['err']->add($GLOBALS['_LANG']['bonus_is_used']);
                } else {
                    //红包被其他人使用过了。
                    $GLOBALS['err']->add($GLOBALS['_LANG']['bonus_is_used_by_other']);
                }

                return false;
            }
        } else {
            //红包不存在
            $GLOBALS['err']->add($GLOBALS['_LANG']['bonus_not_exist']);
            return false;
        }
    }

    /**
     * 取得用户当前可用红包
     * @param   int $user_id 用户id
     * @param   float $goods_amount 订单商品金额
     * @return  array   红包数组
     */
    function user_bonus($user_id, $goods_amount = 0)
    {
        $day = getdate();
        $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        $sql = "SELECT t.type_id, t.type_name, t.type_money, b.bonus_id " .
            "FROM " . $GLOBALS['ecs']->table('bonus_type') . " AS t," .
            $GLOBALS['ecs']->table('user_bonus') . " AS b " .
            "WHERE t.type_id = b.bonus_type_id " .
            "AND t.use_start_date <= '$today' " .
            "AND t.use_end_date >= '$today' " .
            "AND t.min_goods_amount <= '$goods_amount' " .
            "AND b.user_id<>0 " .
            "AND b.user_id = '$user_id' " .
            "AND b.order_id = 0";
        return $GLOBALS['db']->getAll($sql);
    }

    /**
     * 取得红包信息
     * @param   int $bonus_id 红包id
     * @param   string $bonus_sn 红包序列号
     * @param   array   红包信息
     */
    function bonus_info($bonus_id, $bonus_sn = '')
    {
        $sql = "SELECT t.*, b.* " .
            "FROM " . $GLOBALS['ecs']->table('bonus_type') . " AS t," .
            $GLOBALS['ecs']->table('user_bonus') . " AS b " .
            "WHERE t.type_id = b.bonus_type_id ";
        if ($bonus_id > 0) {
            $sql .= "AND b.bonus_id = '$bonus_id'";
        } else {
            $sql .= "AND b.bonus_sn = '$bonus_sn'";
        }

        return $GLOBALS['db']->getRow($sql);
    }

    /**
     * 检查红包是否已使用
     * @param   int $bonus_id 红包id
     * @return  bool
     */
    function bonus_used($bonus_id)
    {
        $sql = "SELECT order_id FROM " . $GLOBALS['ecs']->table('user_bonus') .
            " WHERE bonus_id = '$bonus_id'";

        return $GLOBALS['db']->getOne($sql) > 0;
    }

    /**
     * 设置红包为已使用
     * @param   int $bonus_id 红包id
     * @param   int $order_id 订单id
     * @return  bool
     */
    function use_bonus($bonus_id, $order_id)
    {
        $sql = "UPDATE " . $GLOBALS['ecs']->table('user_bonus') .
            " SET order_id = '$order_id', used_time = '" . gmtime() . "' " .
            "WHERE bonus_id = '$bonus_id' LIMIT 1";

        return $GLOBALS['db']->query($sql);
    }

    /**
     * 设置红包为未使用
     * @param   int $bonus_id 红包id
     * @param   int $order_id 订单id
     * @return  bool
     */
    function unuse_bonus($bonus_id)
    {
        $sql = "UPDATE " . $GLOBALS['ecs']->table('user_bonus') .
            " SET order_id = 0, used_time = 0 " .
            "WHERE bonus_id = '$bonus_id' LIMIT 1";

        return $GLOBALS['db']->query($sql);
    }

    /**
     * 取得当前用户应该得到的红包总额
     */
    function get_total_bonus()
    {
        $day = getdate();
        $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        // 按商品发的红包
        $sql = "SELECT SUM(c.goods_number * t.type_money)" .
            "FROM " . $GLOBALS['ecs']->table('cart') . " AS c, "
            . $GLOBALS['ecs']->table('bonus_type') . " AS t, "
            . $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE c.session_id = '" . SESS_ID . "' " .
            "AND c.is_gift = 0 " .
            "AND c.goods_id = g.goods_id " .
            "AND g.bonus_type_id = t.type_id " .
            "AND t.send_type = '" . SEND_BY_GOODS . "' " .
            "AND t.send_start_date <= '$today' " .
            "AND t.send_end_date >= '$today' " .
            "AND c.rec_type = '" . CART_GENERAL_GOODS . "'";
        $goods_total = floatval($GLOBALS['db']->getOne($sql));

        // 取得购物车中非赠品总金额
        $sql = "SELECT SUM(goods_price * goods_number) " .
            "FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            " AND is_gift = 0 " .
            " AND rec_type = '" . CART_GENERAL_GOODS . "'";
        $amount = floatval($GLOBALS['db']->getOne($sql));

        // 按订单发的红包
        $sql = "SELECT FLOOR('$amount' / min_amount) * type_money " .
            "FROM " . $GLOBALS['ecs']->table('bonus_type') .
            " WHERE send_type = '" . SEND_BY_ORDER . "' " .
            " AND send_start_date <= '$today' " .
            "AND send_end_date >= '$today' " .
            "AND min_amount > 0 ";
        $order_total = floatval($GLOBALS['db']->getOne($sql));

        return $goods_total + $order_total;
    }

    /**
     * 处理红包（下订单时设为使用，取消（无效，退货）订单时设为未使用
     * @param   int $bonus_id 红包编号
     * @param   int $order_id 订单号
     * @param   int $is_used 是否使用了
     */
    function change_user_bonus($bonus_id, $order_id, $is_used = true)
    {
        if ($is_used) {
            $sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . ' SET ' .
                'used_time = ' . gmtime() . ', ' .
                "order_id = '$order_id' " .
                "WHERE bonus_id = '$bonus_id'";
        } else {
            $sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . ' SET ' .
                'used_time = 0, ' .
                'order_id = 0 ' .
                "WHERE bonus_id = '$bonus_id'";
        }
        $GLOBALS['db']->query($sql);
    }


}