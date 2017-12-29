<?php

namespace App\Services;


class UserService
{

    /**
     * 初始化会员数据整合类
     *
     * @access  public
     * @return  object
     */
    function init_users()
    {
        static $cls = null;
        if ($cls != null) {
            return $cls;
        }

        $cfg = unserialize($GLOBALS['_CFG']['integrate_config']);
        $cls = new \App\Plugins\Integrates\Passport($GLOBALS['_CFG']['integrate_code'], $cfg);

        return $cls;
    }

    /**
     * 修改个人资料（Email, 性别，生日)
     *
     * @access  public
     * @param   array $profile array_keys(user_id int, email string, sex int, birthday string);
     *
     * @return  boolen      $bool
     */
    function edit_profile($profile)
    {
        if (empty($profile['user_id'])) {
            $GLOBALS['err']->add($GLOBALS['_LANG']['not_login']);

            return false;
        }

        $cfg = [];
        $cfg['username'] = $GLOBALS['db']->getOne("SELECT user_name FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id='" . $profile['user_id'] . "'");
        if (isset($profile['sex'])) {
            $cfg['gender'] = intval($profile['sex']);
        }
        if (!empty($profile['email'])) {
            if (!is_email($profile['email'])) {
                $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], $profile['email']));

                return false;
            }
            $cfg['email'] = $profile['email'];
        }
        if (!empty($profile['birthday'])) {
            $cfg['bday'] = $profile['birthday'];
        }


        if (!$GLOBALS['user']->edit_user($cfg)) {
            if ($GLOBALS['user']->error == ERR_EMAIL_EXISTS) {
                $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_exist'], $profile['email']));
            } else {
                $GLOBALS['err']->add('DB ERROR!');
            }

            return false;
        }

        // 过滤非法的键值
        $other_key_array = ['msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone'];
        foreach ($profile['other'] as $key => $val) {
            //删除非法key值
            if (!in_array($key, $other_key_array)) {
                unset($profile['other'][$key]);
            } else {
                $profile['other'][$key] = htmlspecialchars(trim($val)); //防止用户输入javascript代码
            }
        }
        // 修改在其他资料
        if (!empty($profile['other'])) {
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $profile['other'], 'UPDATE', "user_id = '$profile[user_id]'");
        }

        return true;
    }

    /**
     * 获取用户帐号信息
     *
     * @access  public
     * @param   int $user_id 用户user_id
     *
     * @return void
     */
    function get_profile($user_id)
    {
        global $user;


        // 会员帐号信息
        $info = [];
        $infos = [];
        $sql = "SELECT user_name, birthday, sex, question, answer, rank_points, pay_points,user_money, user_rank," .
            " msn, qq, office_phone, home_phone, mobile_phone, passwd_question, passwd_answer " .
            "FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'";
        $infos = $GLOBALS['db']->getRow($sql);
        $infos['user_name'] = addslashes($infos['user_name']);

        $row = $user->get_profile_by_name($infos['user_name']); //获取用户帐号信息
        session(['email' =>  $row['email']]);    //注册SESSION

        // 会员等级
        if ($infos['user_rank'] > 0) {
            $sql = "SELECT rank_id, rank_name, discount FROM " . $GLOBALS['ecs']->table('user_rank') .
                " WHERE rank_id = '$infos[user_rank]'";
        } else {
            $sql = "SELECT rank_id, rank_name, discount, min_points" .
                " FROM " . $GLOBALS['ecs']->table('user_rank') .
                " WHERE min_points<= " . intval($infos['rank_points']) . " ORDER BY min_points DESC";
        }

        if ($row = $GLOBALS['db']->getRow($sql)) {
            $info['rank_name'] = $row['rank_name'];
        } else {
            $info['rank_name'] = $GLOBALS['_LANG']['undifine_rank'];
        }

        $cur_date = date('Y-m-d H:i:s');

        // 会员红包
        $bonus = [];
        $sql = "SELECT type_name, type_money " .
            "FROM " . $GLOBALS['ecs']->table('bonus_type') . " AS t1, " . $GLOBALS['ecs']->table('user_bonus') . " AS t2 " .
            "WHERE t1.type_id = t2.bonus_type_id AND t2.user_id = '$user_id' AND t1.use_start_date <= '$cur_date' " .
            "AND t1.use_end_date > '$cur_date' AND t2.order_id = 0";
        $bonus = $GLOBALS['db']->getAll($sql);
        if ($bonus) {
            for ($i = 0, $count = count($bonus); $i < $count; $i++) {
                $bonus[$i]['type_money'] = price_format($bonus[$i]['type_money'], false);
            }
        }

        $info['discount'] = session('discount') * 100 . "%";
        $info['email'] = session('email');
        $info['user_name'] = session('user_name');
        $info['rank_points'] = isset($infos['rank_points']) ? $infos['rank_points'] : '';
        $info['pay_points'] = isset($infos['pay_points']) ? $infos['pay_points'] : 0;
        $info['user_money'] = isset($infos['user_money']) ? $infos['user_money'] : 0;
        $info['sex'] = isset($infos['sex']) ? $infos['sex'] : 0;
        $info['birthday'] = isset($infos['birthday']) ? $infos['birthday'] : '';
        $info['question'] = isset($infos['question']) ? htmlspecialchars($infos['question']) : '';

        $info['user_money'] = price_format($info['user_money'], false);
        $info['pay_points'] = $info['pay_points'] . $GLOBALS['_CFG']['integral_name'];
        $info['bonus'] = $bonus;
        $info['qq'] = $infos['qq'];
        $info['msn'] = $infos['msn'];
        $info['office_phone'] = $infos['office_phone'];
        $info['home_phone'] = $infos['home_phone'];
        $info['mobile_phone'] = $infos['mobile_phone'];
        $info['passwd_question'] = $infos['passwd_question'];
        $info['passwd_answer'] = $infos['passwd_answer'];

        return $info;
    }

    /**
     * 取得用户信息
     * @param   int $user_id 用户id
     * @return  array   用户信息
     */
    function user_info($user_id)
    {
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('users') .
            " WHERE user_id = '$user_id'";
        $user = $GLOBALS['db']->getRow($sql);

        unset($user['question']);
        unset($user['answer']);

        // 格式化帐户余额
        if ($user) {
            //        if ($user['user_money'] < 0)
//        {
//            $user['user_money'] = 0;
//        }
            $user['formated_user_money'] = price_format($user['user_money'], false);
            $user['formated_frozen_money'] = price_format($user['frozen_money'], false);
        }

        return $user;
    }

    /**
     * 修改用户
     * @param   int $user_id 订单id
     * @param   array $user key => value
     * @return  bool
     */
    function update_user($user_id, $user)
    {
        return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'),
            $user, 'UPDATE', "user_id = '$user_id'");
    }

    /**
     * 取得用户等级信息
     * @access   public
     * @author   Xuan Yan
     *
     * @return array
     */
    function get_rank_info()
    {
        if (!empty(session('user_rank'))) {
            $sql = "SELECT rank_name, special_rank FROM " . $GLOBALS['ecs']->table('user_rank') . " WHERE rank_id = '" . session('user_rank') . "'";
            $row = $GLOBALS['db']->getRow($sql);
            if (empty($row)) {
                return [];
            }
            $rank_name = $row['rank_name'];
            if ($row['special_rank']) {
                return ['rank_name' => $rank_name];
            } else {
                $user_rank = $GLOBALS['db']->getOne("SELECT rank_points FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '" . session('user_id') . "'");
                $sql = "SELECT rank_name,min_points FROM " . $GLOBALS['ecs']->table('user_rank') . " WHERE min_points > '$user_rank' ORDER BY min_points ASC LIMIT 1";
                $rt = $GLOBALS['db']->getRow($sql);
                $next_rank_name = $rt['rank_name'];
                $next_rank = $rt['min_points'] - $user_rank;
                return ['rank_name' => $rank_name, 'next_rank_name' => $next_rank_name, 'next_rank' => $next_rank];
            }
        } else {
            return [];
        }
    }


    /**
     * 获取用户中心默认页面所需的数据
     *
     * @access  public
     * @param   int $user_id 用户ID
     *
     * @return  array       $info               默认页面所需资料数组
     */
    function get_user_default($user_id)
    {
        $user_bonus = get_user_bonus();

        $sql = "SELECT pay_points, user_money, credit_line, last_login, is_validated FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'";
        $row = $GLOBALS['db']->getRow($sql);
        $info = [];
        $info['username'] = stripslashes(session('user_name'));
        $info['shop_name'] = $GLOBALS['_CFG']['shop_name'];
        $info['integral'] = $row['pay_points'] . $GLOBALS['_CFG']['integral_name'];
        // 增加是否开启会员邮件验证开关
        $info['is_validate'] = ($GLOBALS['_CFG']['member_email_validate'] && !$row['is_validated']) ? 0 : 1;
        $info['credit_line'] = $row['credit_line'];
        $info['formated_credit_line'] = price_format($info['credit_line'], false);

        //如果$_SESSION中时间无效说明用户是第一次登录。取当前登录时间。
        $last_time = session()->has('last_time') ? session('last_time') : session('last_login');

        if ($last_time == 0) {
            $last_time = gmtime();
            session(['last_time' =>  $last_time]);
        }

        $info['last_time'] = local_date($GLOBALS['_CFG']['time_format'], $last_time);
        $info['surplus'] = price_format($row['user_money'], false);
        $info['bonus'] = sprintf($GLOBALS['_LANG']['user_bonus_info'], $user_bonus['bonus_count'], price_format($user_bonus['bonus_value'], false));

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info') .
            " WHERE user_id = '" . $user_id . "' AND add_time > '" . local_strtotime('-1 months') . "'";
        $info['order_count'] = $GLOBALS['db']->getOne($sql);

        load_helper('order');
        $sql = "SELECT order_id, order_sn " .
            " FROM " . $GLOBALS['ecs']->table('order_info') .
            " WHERE user_id = '" . $user_id . "' AND shipping_time > '" . $last_time . "'" . order_query_sql('shipped');
        $info['shipped_order'] = $GLOBALS['db']->getAll($sql);

        return $info;
    }


    /**
     *  获取用户参与活动信息
     *
     * @access  public
     * @param   int $user_id 用户id
     *
     * @return  array
     */
    function get_user_prompt($user_id)
    {
        $prompt = [];
        $now = gmtime();
        // 夺宝奇兵
        $sql = "SELECT act_id, goods_name, end_time " .
            "FROM " . $GLOBALS['ecs']->table('goods_activity') .
            " WHERE act_type = '" . GAT_SNATCH . "'" .
            " AND (is_finished = 1 OR (is_finished = 0 AND end_time <= '$now'))";
        $res = $GLOBALS['db']->query($sql);
        foreach ($res as $row) {
            $act_id = $row['act_id'];
            $result = get_snatch_result($act_id);
            if (isset($result['order_count']) && $result['order_count'] == 0 && $result['user_id'] == $user_id) {
                $prompt[] = [
                    'text' => sprintf($GLOBALS['_LANG']['your_snatch'], $row['goods_name'], $row['act_id']),
                    'add_time' => $row['end_time']
                ];
            }
            if (isset($auction['last_bid']) && $auction['last_bid']['bid_user'] == $user_id && $auction['order_count'] == 0) {
                $prompt[] = [
                    'text' => sprintf($GLOBALS['_LANG']['your_auction'], $row['goods_name'], $row['act_id']),
                    'add_time' => $row['end_time']
                ];
            }
        }


        // 竞拍

        $sql = "SELECT act_id, goods_name, end_time " .
            "FROM " . $GLOBALS['ecs']->table('goods_activity') .
            " WHERE act_type = '" . GAT_AUCTION . "'" .
            " AND (is_finished = 1 OR (is_finished = 0 AND end_time <= '$now'))";
        $res = $GLOBALS['db']->query($sql);
        foreach ($res as $row) {
            $act_id = $row['act_id'];
            $auction = auction_info($act_id);
            if (isset($auction['last_bid']) && $auction['last_bid']['bid_user'] == $user_id && $auction['order_count'] == 0) {
                $prompt[] = [
                    'text' => sprintf($GLOBALS['_LANG']['your_auction'], $row['goods_name'], $row['act_id']),
                    'add_time' => $row['end_time']
                ];
            }
        }

        // 排序
        usort($prompt, function ($a, $b) {
            if ($a["add_time"] == $b["add_time"]) {
                return 0;
            };
            return $a["add_time"] < $b["add_time"] ? 1 : -1;
        });

        // 格式化时间
        foreach ($prompt as $key => $val) {
            $prompt[$key]['formated_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['add_time']);
        }

        return $prompt;
    }
    /**
     * 更新用户SESSION,COOKIE及登录时间、登录次数。
     *
     * @access  public
     * @return  void
     */
    function update_user_info()
    {
        if (!session()->has('user_id')) {
            return false;
        }

        // 查询会员信息
        $time = date('Y-m-d');
        $sql = 'SELECT u.user_money,u.email, u.pay_points, u.user_rank, u.rank_points, ' .
            ' IFNULL(b.type_money, 0) AS user_bonus, u.last_login, u.last_ip' .
            ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' .
            ' LEFT JOIN ' . $GLOBALS['ecs']->table('user_bonus') . ' AS ub' .
            ' ON ub.user_id = u.user_id AND ub.used_time = 0 ' .
            ' LEFT JOIN ' . $GLOBALS['ecs']->table('bonus_type') . ' AS b' .
            " ON b.type_id = ub.bonus_type_id AND b.use_start_date <= '$time' AND b.use_end_date >= '$time' " .
            " WHERE u.user_id = '" . session('user_id') . "'";
        if ($row = $GLOBALS['db']->getRow($sql)) {
            // 更新SESSION
            session(['last_time' =>  $row['last_login']]);
            session(['last_ip' =>  $row['last_ip']]);
            session(['login_fail' =>  0]);
            session(['email' =>  $row['email']]);

            /*判断是否是特殊等级，可能后台把特殊会员组更改普通会员组*/
            if ($row['user_rank'] > 0) {
                $sql = "SELECT special_rank from " . $GLOBALS['ecs']->table('user_rank') . "where rank_id='$row[user_rank]'";
                if ($GLOBALS['db']->getOne($sql) === '0' || $GLOBALS['db']->getOne($sql) === null) {
                    $sql = "update " . $GLOBALS['ecs']->table('users') . "set user_rank='0' where user_id='" . session('user_id') . "'";
                    $GLOBALS['db']->query($sql);
                    $row['user_rank'] = 0;
                }
            }

            // 取得用户等级和折扣
            if ($row['user_rank'] == 0) {
                // 非特殊等级，根据等级积分计算用户等级（注意：不包括特殊等级）
                $sql = 'SELECT rank_id, discount FROM ' . $GLOBALS['ecs']->table('user_rank') . " WHERE special_rank = '0' AND min_points <= " . intval($row['rank_points']) . ' AND max_points > ' . intval($row['rank_points']);
                if ($row = $GLOBALS['db']->getRow($sql)) {
                    session(['user_rank' =>  $row['rank_id']]);
                    session(['discount' =>  $row['discount'] / 100.00]);
                } else {
                    session(['user_rank' =>  0]);
                    session(['discount' =>  1]);
                }
            } else {
                // 特殊等级
                $sql = 'SELECT rank_id, discount FROM ' . $GLOBALS['ecs']->table('user_rank') . " WHERE rank_id = '$row[user_rank]'";
                if ($row = $GLOBALS['db']->getRow($sql)) {
                    session(['user_rank' =>  $row['rank_id']]);
                    session(['discount' =>  $row['discount'] / 100.00]);
                } else {
                    session(['user_rank' =>  0]);
                    session(['discount' =>  1]);
                }
            }
        }

        // 更新登录时间，登录次数及登录ip
        $sql = "UPDATE " . $GLOBALS['ecs']->table('users') . " SET" .
            " visit_count = visit_count + 1, " .
            " last_ip = '" . real_ip() . "'," .
            " last_login = '" . gmtime() . "'" .
            " WHERE user_id = '" . session('user_id') . "'";
        $GLOBALS['db']->query($sql);
    }

    /**
     *  获取用户信息数组
     *
     * @access  public
     * @param
     *
     * @return array        $user       用户信息数组
     */
    function get_user_info($id = 0)
    {
        if ($id == 0) {
            $id = session('user_id');
        }
        $time = date('Y-m-d');
        $sql = 'SELECT u.user_id, u.email, u.user_name, u.user_money, u.pay_points' .
            ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' .
            " WHERE u.user_id = '$id'";
        $user = $GLOBALS['db']->getRow($sql);
        $bonus = get_user_bonus($id);

        $user['username'] = $user['user_name'];
        $user['user_points'] = $user['pay_points'] . $GLOBALS['_CFG']['integral_name'];
        $user['user_money'] = price_format($user['user_money'], false);
        $user['user_bonus'] = price_format($bonus['bonus_value'], false);

        return $user;
    }

}