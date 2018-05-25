<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：UserModel.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTouch 用户模型
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class UsersModel extends BaseModel {

    protected $table = 'users';

    /**
     * 更新用户SESSION,COOKIE及登录时间、登录次数。
     *
     * @access  public
     * @return  void
     */
    function update_user_info() {
        if (!$_SESSION['user_id']) {
            return false;
        }

        /* 查询会员信息 */
        $time = date('Y-m-d');
        $sql = 'SELECT u.user_money,u.email, u.pay_points, u.user_rank, u.rank_points, ' .
                ' IFNULL(b.type_money, 0) AS user_bonus, u.last_login, u.last_ip' .
                ' FROM ' . $this->pre . 'users AS u ' .
                ' LEFT JOIN ' . $this->pre . 'user_bonus AS ub' .
                ' ON ub.user_id = u.user_id AND ub.used_time = 0 ' .
                ' LEFT JOIN ' . $this->pre . 'bonus_type AS b' .
                " ON b.type_id = ub.bonus_type_id AND b.use_start_date <= '$time' AND b.use_end_date >= '$time' " .
                " WHERE u.user_id = '$_SESSION[user_id]'";
        if ($row = $this->row($sql)) {
            /* 更新SESSION */
            $_SESSION['last_time'] = $row['last_login'];
            $_SESSION['last_ip'] = $row['last_ip'];
            $_SESSION['login_fail'] = 0;
            $_SESSION['email'] = $row['email'];

            /* 判断是否是特殊等级，可能后台把特殊会员组更改普通会员组 */
            if ($row['user_rank'] > 0) {
                $sql = "SELECT special_rank from " . $this->pre . "user_rank where rank_id='$row[user_rank]'";
                $res = $this->row($sql);
                if ($res['special_rank'] === '0' || $res['special_rank'] === null) {
                    $sql = "update " . $this->pre . "users set user_rank='0' where user_id='$_SESSION[user_id]'";
                    $this->query($sql);
                    $row['user_rank'] = 0;
                }
            }
            /* 取得用户等级和折扣 */
            if ($row['user_rank'] == 0) {
                // 非特殊等级，根据等级积分计算用户等级（注意：不包括特殊等级）
                $sql = 'SELECT rank_id, discount FROM ' . $this->pre . "user_rank WHERE special_rank = '0' AND min_points <= " . intval($row['rank_points']) . ' AND max_points > ' . intval($row['rank_points']);
                if ($row = $this->row($sql)) {
                    $_SESSION['user_rank'] = $row['rank_id'];
                    $_SESSION['discount'] = $row['discount'] / 100.00;
                } else {
                    $_SESSION['user_rank'] = 0;
                    $_SESSION['discount'] = 1;
                }
            } else {
                // 特殊等级
                $sql = 'SELECT rank_id, discount FROM ' . $this->pre . "user_rank WHERE rank_id = '$row[user_rank]'";
                if ($row = $this->row($sql)) {
                    $_SESSION['user_rank'] = $row['rank_id'];
                    $_SESSION['discount'] = $row['discount'] / 100.00;
                } else {
                    $_SESSION['user_rank'] = 0;
                    $_SESSION['discount'] = 1;
                }
            }
        }

        /* 更新登录时间，登录次数及登录ip */
        $sql = "UPDATE " . $this->pre . "users SET" .
                " visit_count = visit_count + 1, " .
                " last_ip = '" . real_ip() . "'," .
                " last_login = '" . gmtime() . "'" .
                " WHERE user_id = '" . $_SESSION['user_id'] . "'";
        $this->query($sql);

        //更新绑定微信粉丝信息
        if($_SESSION['unionid'] && $_SESSION['user_id']) {
            $res = $this->model->table('wechat_user')->field('ect_uid, nickname, sex, province, city, country, headimgurl, unionid')->where(array('unionid' => $_SESSION['unionid']))->find();
            if(!$res['ect_uid']){
                //更新wechat_user表ect_uid
                $sql = 'UPDATE ' . $this->pre . 'wechat_user SET ect_uid = ' . $_SESSION['user_id'] . ' WHERE unionid = ' ."'".$_SESSION['unionid']."'";
                $this->query($sql);
                
                //插入connect_user表
                $res['user_id'] = $_SESSION['user_id'];
                $this->update_connnect_user($res, 'user_login'); 
            }
        }
    }

    /**
     * 用户注册，登录函数
     *
     * @access  public
     * @param   string       $username          注册用户名
     * @param   string       $password          用户密码
     * @param   string       $email             注册email
     * @param   array        $other             注册的其他信息
     *
     * @return  bool         $bool
     */
    function register($username, $password, $email, $other = array()) {
        /* 检查注册是否关闭 */
        $shop_reg_closed = C('shop_reg_closed');
        if (!empty($shop_reg_closed)) {
            ECTouch::err()->add(L('shop_register_closed'));
        }
        /* 检查username */
        if (empty($username)) {
            ECTouch::err()->add(L('username_empty'));
        } else {
            if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username)) {
                ECTouch::err()->add(sprintf(L('username_invalid'), htmlspecialchars($username)));
            }
        }

        /* 检查email */
        if (empty($email)) {
            ECTouch::err()->add(L('email_empty'));
        } else {
            if (!is_email($email)) {
                ECTouch::err()->add(sprintf(L('email_invalid'), htmlspecialchars($email)));
            }
        }

        if (ECTouch::err()->error_no > 0) {
            return false;
        }
        //判断用户名是否是手机号格式
        if (is_mobile($username)) {
            $mobile = 1;
        }

        /* 检查是否和管理员重名 */
        if (model('Users')->admin_registered($username)) {
            ECTouch::err()->add(sprintf(L('username_exist'), $username));
            return false;
        }

        if (!ECTouch::user()->add_user($username, $password, $email)) {
            if (ECTouch::user()->error == ERR_INVALID_USERNAME) {
                ECTouch::err()->add(sprintf(L('username_invalid'), $username));
            } elseif (ECTouch::user()->error == ERR_USERNAME_NOT_ALLOW) {
                ECTouch::err()->add(sprintf(L('username_not_allow'), $username));
            } elseif (ECTouch::user()->error == ERR_USERNAME_EXISTS) {
                ECTouch::err()->add(sprintf(L('username_exist'), $username));
            } elseif (ECTouch::user()->error == ERR_INVALID_EMAIL) {
                ECTouch::err()->add(sprintf(L('email_invalid'), $email));
            } elseif (ECTouch::user()->error == ERR_EMAIL_NOT_ALLOW) {
                ECTouch::err()->add(sprintf(L('email_not_allow'), $email));
            } elseif (ECTouch::user()->error == ERR_EMAIL_EXISTS) {
                ECTouch::err()->add(sprintf(L('email_exist'), $email));
            } else {
                ECTouch::err()->add('UNKNOWN ERROR!');
            }

            //注册失败
            return false;
        } else {
            //注册成功

            /* 设置成登录状态 */
            ECTouch::user()->set_session($username);
            ECTouch::user()->set_cookie($username);

            /* 注册送积分 */
            $register_points = C('register_points');
            if (!empty($register_points)) {
                model('ClipsBase')->log_account_change($_SESSION['user_id'], 0, 0, C('register_points'), C('register_points'), L('register_points'));
            }

            //定义other合法的变量数组
            $other_key_array = array('msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone', 'parent_id', 'aite_id');
            $update_data['reg_time'] = local_strtotime(local_date('Y-m-d H:i:s'));
            if ($other) {
                foreach ($other as $key => $val) {
                    //删除非法key值
                    if (!in_array($key, $other_key_array)) {
                        unset($other[$key]);
                    } else {
                        $other[$key] = htmlspecialchars(trim($val)); //防止用户输入javascript代码
                    }
                }
                $update_data = array_merge($update_data, $other);
            }
            $parent_id = $update_data['parent_id'];
            $condition['user_id'] = $_SESSION['user_id'];
            $this->update($condition, $update_data);
            $where['mobile_phone'] = $username;
            $res = $this->model->table('users')
                        ->field('user_id')
                        ->where($where)
                        ->select();
            //用户名是手机号格式时且并不存在此手机号则把用户插入到手机号字段中
            if(($mobile == 1) && empty($res)){
                $sql = 'UPDATE ' . $this->pre . 'users SET mobile_phone = "' . $username . '" WHERE user_id = "' . $_SESSION['user_id'] . '"';
                $this->query($sql);
            }
            /* 推荐处理 */
            $affiliate = unserialize(C('affiliate'));
            if (isset($affiliate['on']) && $affiliate['on'] == 1) {
                // 推荐开关开启
                $up_uid = get_affiliate();
                empty($affiliate) && $affiliate = array();
                $affiliate['config']['level_register_all'] = intval($affiliate['config']['level_register_all']);
                $affiliate['config']['level_register_up'] = intval($affiliate['config']['level_register_up']);
                if ($up_uid || $parent_id) {
                    if (!empty($affiliate['config']['level_register_all'])) {
                        if (!empty($affiliate['config']['level_register_up'])) {
                            $up_uid =  $up_uid > 0 ? $up_uid : $parent_id;
                            $res = $this->row("SELECT rank_points FROM " . $this->pre . "users WHERE user_id = '$up_uid'");
                            if ($res['rank_points'] + $affiliate['config']['level_register_all'] <= $affiliate['config']['level_register_up']) {
                                model('ClipsBase')->log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, sprintf(L('register_affiliate'), $_SESSION['user_id'], $username));
                            }
                        } else {
                            model('ClipsBase')->log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, L('register_affiliate'));
                        }
                    }
                    /*DRP_START*/
                    $sql = "SELECT id FROM " . $this->pre . "drp_shop where user_id = ".$up_uid;
                    $res = $this->row($sql);
                    if(!empty($res)){
                    /*DRP_END*/
                    //设置推荐人
                    $sql = 'UPDATE ' . $this->pre . 'users SET parent_id = ' . $up_uid . ' WHERE user_id = ' . $_SESSION['user_id'];
                    $this->query($sql);
                    /*DRP_START*/
                    }
                    /*DRP_END*/
                }
            }

            model('Users')->update_user_info();      // 更新用户信息
            model('Users')->recalculate_price();     // 重新计算购物车中的商品价格

            return true;
        }
    }

    /**
     *  发送激活验证邮件
     *
     * @access  public
     * @param   int     $user_id        用户ID
     *
     * @return boolen
     */
    function send_regiter_hash($user_id) {
        /* 设置验证邮件模板所需要的内容信息 */
        $template = model('Base')->get_mail_template('register_validate');
        $hash = model('Users')->register_hash('encode', $user_id);
        $validate_email = __HOST__ . url('user/validate_email', array('hash' => $hash)); //ECTouch::ecs()->url() . 'user.php?act=validate_email&hash=' . $hash;

        $sql = "SELECT user_name, email FROM " . $this->pre . "users WHERE user_id = '$user_id'";
        $row = $this->row($sql);

        ECTouch::view()->assign('user_name', $row['user_name']);
        ECTouch::view()->assign('validate_email', $validate_email);
        ECTouch::view()->assign('shop_name', C('shop_name'));
        ECTouch::view()->assign('send_date', date(C('date_format')));

        $content = ECTouch::view()->fetch('str:' . $template['template_content']);

        /* 发送激活验证邮件 */
        if (send_mail($row['user_name'], $row['email'], $template['template_subject'], $content, $template['is_html'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  生成邮件验证hash
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function register_hash($operation, $key) {
        if ($operation == 'encode') {
            $user_id = intval($key);
            $sql = "SELECT reg_time " .
                    " FROM " . $this->pre .
                    "users WHERE user_id = '$user_id' LIMIT 1";
            $res = $this->row($sql);
            $reg_time = $res['reg_time'];
            $hash = substr(md5($user_id . C('hash_code') . $reg_time), 16, 4);

            return base64_encode($user_id . ',' . $hash);
        } else {
            $hash = base64_decode(trim($key));
            $row = explode(',', $hash);
            if (count($row) != 2) {
                return 0;
            }
            $user_id = intval($row[0]);
            $salt = trim($row[1]);

            if ($user_id <= 0 || strlen($salt) != 4) {
                return 0;
            }

            $sql = "SELECT reg_time " .
                    " FROM " . $this->pre .
                    "users WHERE user_id = '$user_id' LIMIT 1";
            $res = $this->row($sql);
            $reg_time = $res['reg_time'];
            $pre_salt = substr(md5($user_id . C('hash_code') . $reg_time), 16, 4);

            if ($pre_salt == $salt) {
                return $user_id;
            } else {
                return 0;
            }
        }
    }

    /**
     * 判断超级管理员用户名是否存在
     * @param   string      $adminname 超级管理员用户名
     * @return  boolean
     */
    function admin_registered($adminname) {
        $sql = "SELECT COUNT(*) as count FROM " . $this->pre .
                "admin_user WHERE user_name = '$adminname'";
        $res = $this->row($sql);
        return $res['count'];
    }

    /**
     * 修改个人资料（Email, 性别，生日)
     *
     * @access  public
     * @param   array       $profile       array_keys(user_id int, email string, sex int, birthday string);
     *
     * @return  boolen      $bool
     */
    function edit_profile($profile) {
        if (empty($profile['user_id'])) {
            ECTouch::err()->add(L('not_login'));
            return false;
        }

        $cfg = array();
        $sql = "SELECT user_name FROM " . $this->pre . "users WHERE user_id='" . $profile['user_id'] . "'";
        $res = $this->row($sql);
        $cfg['username'] = $res['user_name'];
        if (isset($profile['sex'])) {
            $cfg['gender'] = intval($profile['sex']);
        }
        if (!empty($profile['email'])) {
            if (!is_email($profile['email'])) {
                ECTouch::err()->add(sprintf(L('email_invalid'), $profile['email']));

                return false;
            }
            $cfg['email'] = $profile['email'];
        }
        if (!empty($profile['birthday'])) {
            $cfg['bday'] = $profile['birthday'];
        }


        if (!ECTouch::user()->edit_user($cfg)) {
            if (ECTouch::user()->error == ERR_EMAIL_EXISTS) {
                ECTouch::err()->add(sprintf(L('email_exist'), $profile['email']));
            } else {
                ECTouch::err()->add('DB ERROR!');
            }

            return false;
        }

        /* 过滤非法的键值 */
        $other_key_array = array('msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone');
        foreach ($profile['other'] as $key => $val) {
            //删除非法key值
            if (!in_array($key, $other_key_array)) {
                unset($profile['other'][$key]);
            } else {
                $profile['other'][$key] = htmlspecialchars(trim($val)); //防止用户输入javascript代码
            }
        }
        /* 修改在其他资料 */
        if (!empty($profile['other'])) {
            $condition['user_id'] = $profile['user_id'];
            $this->update($condition, $profile['other']);
        }

        return true;
    }

    /**
     * 获取用户帐号信息
     *
     * @access  public
     * @param   int       $user_id        用户user_id
     *
     * @return void
     */
    function get_profile($user_id) {

        /* 会员帐号信息 */
        $info = array();
        $infos = array();
        $sql = "SELECT user_name, birthday, sex, question, answer, rank_points, pay_points,user_money, user_rank," .
                " msn, qq, office_phone, home_phone, mobile_phone, passwd_question, passwd_answer " .
                "FROM " . $this->pre . "users WHERE user_id = '$user_id'";
        $infos = $this->row($sql);
        $infos['user_name'] = addslashes($infos['user_name']);

        $row = ECTouch::user()->get_profile_by_name($infos['user_name']); //获取用户帐号信息
        $_SESSION['email'] = $row['email'];    //注册SESSION

        /* 会员等级 */
        if ($infos['user_rank'] > 0) {
            $sql = "SELECT rank_id, rank_name, discount FROM " . $this->pre .
                    "user_rank WHERE rank_id = '$infos[user_rank]'";
        } else {
            $sql = "SELECT rank_id, rank_name, discount, min_points" .
                    " FROM " . $this->pre .
                    "user_rank WHERE min_points<= " . intval($infos['rank_points']) . " ORDER BY min_points DESC";
        }

        if ($row = $this->row($sql)) {
            $info['rank_name'] = $row['rank_name'];
        } else {
            $info['rank_name'] = L('undifine_rank');
        }

        $cur_date = date('Y-m-d H:i:s');

        /* 会员红包 */
        $bonus = array();
        $sql = "SELECT type_name, type_money " .
                "FROM " . $this->pre . "bonus_type AS t1, " . $this->pre . "user_bonus AS t2 " .
                "WHERE t1.type_id = t2.bonus_type_id AND t2.user_id = '$user_id' AND t1.use_start_date <= '$cur_date' " .
                "AND t1.use_end_date > '$cur_date' AND t2.order_id = 0";
        $bonus = $this->query($sql);
        if ($bonus) {
            for ($i = 0, $count = count($bonus); $i < $count; $i++) {
                $bonus[$i]['type_money'] = price_format($bonus[$i]['type_money'], false);
            }
        }

        $info['discount'] = $_SESSION['discount'] * 100 . "%";
        $info['email'] = $_SESSION['email'];
        $info['user_name'] = $_SESSION['user_name'];
        $info['rank_points'] = isset($infos['rank_points']) ? $infos['rank_points'] : '';
        $info['pay_points'] = isset($infos['pay_points']) ? $infos['pay_points'] : 0;
        $info['user_money'] = isset($infos['user_money']) ? $infos['user_money'] : 0;
        $info['sex'] = isset($infos['sex']) ? $infos['sex'] : 0;
        $info['birthday'] = isset($infos['birthday']) ? $infos['birthday'] : '';
        $info['question'] = isset($infos['question']) ? htmlspecialchars($infos['question']) : '';

        $info['user_money'] = price_format($info['user_money'], false);
        $info['pay_points'] = $info['pay_points'] . C('integral_name');
        $info['bonus'] = $bonus;
        $info['qq'] = $infos['qq'];
        $info['msn'] = $infos['msn'];
        $info['office_phone'] = $infos['office_phone'];
        $info['home_phone'] = $infos['home_phone'];
        $info['mobile_phone'] = $infos['mobile_phone'];
        $info['passwd_question'] = $infos['passwd_question'];
        $info['passwd_answer'] = $infos['passwd_answer'];
        $info['user_rank'] = $infos['user_rank'];

        return $info;
    }

    /**
     * 取得收货人地址列表
     * @param   int     $user_id    用户编号
     * @param   int     $id         收货地址id
     * @return  array
     */
    function get_consignee_list($user_id, $id = 0, $num = 10, $start = 0) {
        if ($id) {
            $where['user_id'] = $user_id;
            $where['address_id'] = $id;
            $this->table = 'user_address';
            return $this->find($where);
        } else {
            $sql = 'select ua.*,u.address_id as adds_id from ' . $this->pre . 'user_address as ua left join '. $this->pre . 'users as u on ua.address_id =u.address_id'. ' where ua.user_id = ' . $user_id . ' order by ua.address_id limit ' . $start . ', ' . $num;

            return $this->query($sql);
        }
    }

    /**
     *  给指定用户添加一个指定红包
     *
     * @access  public
     * @param   int         $user_id        用户ID
     * @param   string      $bouns_sn       红包序列号
     *
     * @return  boolen      $result
     */
    function add_bonus($user_id, $bouns_sn) {
        if (empty($user_id)) {
            ECTouch::err()->add(L('not_login'));

            return false;
        }
        /* 查询红包序列号是否已经存在 */
        $sql = "SELECT bonus_id, bonus_sn, user_id, bonus_type_id FROM " . $this->pre .
                "user_bonus WHERE bonus_sn = '$bouns_sn'";
        $row = $this->row($sql);
        if ($row) {
            if ($row['user_id'] == 0) {
                //红包没有被使用
                $sql = "SELECT send_end_date, use_end_date " .
                        " FROM " . $this->pre .
                        "bonus_type WHERE type_id = '" . $row['bonus_type_id'] . "'";

                $bonus_time = $this->row($sql);

                $now = gmtime();
                if ($now > $bonus_time['use_end_date']) {
                    ECTouch::err()->add(L('bonus_use_expire'));
                    return false;
                }

                $sql = "UPDATE " . $this->pre . "user_bonus SET user_id = '$user_id' " .
                        "WHERE bonus_id = '$row[bonus_id]'";
                $result = $this->query($sql);
                if ($result) {
                    return true;
                } else {
                    return M()->errorMsg();
                }
            } else {
                if ($row['user_id'] == $user_id) {
                    //红包已经添加过了。
                    ECTouch::err()->add(L('bonus_is_used'));
                } else {
                    //红包被其他人使用过了。
                    ECTouch::err()->add(L('bonus_is_used_by_other'));
                }

                return false;
            }
        } else {
            //红包不存在
            ECTouch::err()->add(L('bonus_not_exist'));
            return false;
        }
    }

    /**
     *  获取用户指定范围的订单列表
     *
     * @access  public
     * @param   int         $user_id        用户ID号
     * @param   int         $pay            订单状态，0未付款，1全部，默认1
     * @param   int         $num            列表最大数量
     * @param   int         $start          列表起始位置
     * @return  array       $order_list     订单列表
     */
    function get_user_orders($user_id, $pay = 1, $num = 10, $start = 0) {
        /* 取得订单列表 */
        $arr = array();

        if ($pay == 1) {
            $pay = '';
        } elseif($pay == 0) {
            // 未付款 但不包含已取消、无效、退货订单的订单
            $pay = 'and pay_status = ' . PS_UNPAYED . ' and order_status not in(' . OS_CANCELED . ','. OS_INVALID .','. OS_RETURNED .')';
        }else{
            $pay = 'and pay_status = ' . PS_UNPAYED ;
        }

        $sql = "SELECT order_id, order_sn, shipping_id, order_status, shipping_status, pay_status, add_time, " .
                "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee " .
                " FROM " . $this->pre .
                "order_info WHERE user_id = '$user_id' " . $pay . " ORDER BY add_time DESC LIMIT $start , $num";
        $res = M()->query($sql);
        foreach ($res as $key => $value) {
            if ($value['order_status'] == OS_UNCONFIRMED) {
                $value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\" onclick=\"if (!confirm('" . L('confirm_cancel') . "')) return false;\">" . L('cancel') . "</a>";
            } else if ($value['order_status'] == OS_SPLITED) {
                /* 对配送状态的处理 */
                if ($value['shipping_status'] == SS_SHIPPED) {
                    @$value['handler'] = "<a href=\"" . url('user/affirm_received', array('order_id' => $value['order_id'])) . "\" onclick=\"if (!confirm('" . L('confirm_received') . "')) return false;\">" . L('received') . "</a>";
                } elseif ($value['shipping_status'] == SS_RECEIVED) {
                    @$value['handler'] = '<span style="color:red">' . L('ss_received') . '</span>';
                } else {
                    if ($value['pay_status'] == PS_UNPAYED) {
                        @$value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\">" . L('pay_money') . "</a>";
                    } else {
                        @$value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\">" . L('view_order') . "</a>";
                    }
                }
            } else {
                $value['handler'] = '<span>' . L('os.' . $value['order_status']) . '</span>';
            }

            $value['shipping_status'] = ($value['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $value['shipping_status'];
            $value['order_status'] = L('os.' . $value['order_status']) . ',' . L('ps.' . $value['pay_status']) . ',' . L('ss.' . $value['shipping_status']);


            $arr[] = array(
                'order_id' => $value['order_id'],
                'order_sn' => $value['order_sn'],
                'img' => get_image_path(0, model('Order')->get_order_thumb($value['order_id'])),
                'order_time' => local_date(C('time_format'), $value['add_time']),
                'order_status' => $value['order_status'],
                'shipping_id' => $value['shipping_id'],
                'total_fee' => price_format($value['total_fee'], false),
                'url' => url('user/order_detail', array('order_id' => $value['order_id'])),
                'goods_count' => model('Users')->get_order_goods_count($value['order_id']),
                'handler' => $value['handler']
            );

        }
        return $arr;
    }

	/**
     *  获取用户未收货订单列表
     *
     * @access  public
     * @param   int         $user_id        用户ID号
     * @param   int         $pay            订单状态，0未付款，1全部，默认1
     * @param   int         $num            列表最大数量
     * @param   int         $start          列表起始位置
     * @return  array       $order_list     订单列表
     */
    function not_shouhuo_orders($user_id, $pay = 1, $num = 10, $start = 0) {
        /* 取得订单列表 */
        $arr = array();

        if ($pay == 1) {
            $pay = 'and pay_status = ' . PS_PAYED . ' and shipping_status not in(' . SS_RECEIVED .')';


        } else {

            $pay = '';
        }

        $sql = "SELECT order_id, order_sn, shipping_id, order_status, shipping_status, pay_status, add_time, " .
                "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee " .
                " FROM " . $this->pre .
                "order_info WHERE user_id = '$user_id' " . $pay . " ORDER BY add_time DESC LIMIT $start , $num";

        $res = M()->query($sql);




        foreach ($res as $key => $value) {
            if ($value['order_status'] == OS_UNCONFIRMED) {
                $value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\" onclick=\"if (!confirm('" . L('confirm_cancel') . "')) return false;\">" . L('cancel') . "</a>";
            } else if ($value['order_status'] == OS_SPLITED) {
                /* 对配送状态的处理 */
                if ($value['shipping_status'] == SS_SHIPPED) {
                    @$value['handler'] = "<a href=\"" . url('user/affirm_received', array('order_id' => $value['order_id'])) . "\" onclick=\"if (!confirm('" . L('confirm_received') . "')) return false;\">" . L('received') . "</a>";
                } elseif ($value['shipping_status'] == SS_RECEIVED) {
                    @$value['handler'] = '<span style="color:red">' . L('ss_received') . '</span>';
                } else {
                    if ($value['pay_status'] == PS_UNPAYED) {
                        @$value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\">" . L('pay_money') . "</a>";
                    } else {
                        @$value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\">" . L('view_order') . "</a>";
                    }
                }
            } else {
                $value['handler'] = '<span>' . L('os.' . $value['order_status']) . '</span>';
            }

            $value['shipping_status'] = ($value['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $value['shipping_status'];
            $value['order_status'] = L('os.' . $value['order_status']) . ',' . L('ps.' . $value['pay_status']) . ',' . L('ss.' . $value['shipping_status']);



            $arr[] = array('order_id' => $value['order_id'],
                'order_sn' => $value['order_sn'],
                'img' => get_image_path(0, model('Order')->get_order_thumb($value['order_id'])),
                'order_time' => local_date(C('time_format'), $value['add_time']),
                'order_status' => $value['order_status'],
                'shipping_id' => $value['shipping_id'],
                'total_fee' => price_format($value['total_fee'], false),
                'url' => url('user/order_detail', array('order_id' => $value['order_id'])),
                'goods_count' => model('Users')->get_order_goods_count($value['order_id']),
                'handler' => $value['handler']);
        }
        return $arr;
    }
    /**
     * 取消一个用户订单
     *
     * @access  public
     * @param   int         $order_id       订单ID
     * @param   int         $user_id        用户ID
     *
     * @return void
     */
    function cancel_order($order_id, $user_id = 0) {
        /* 查询订单信息，检查状态 */
        $sql = "SELECT user_id, order_id, order_sn , surplus , integral , bonus_id, order_status, shipping_status, pay_status FROM " . $this->pre . "order_info WHERE order_id = '$order_id'";
        $order = $this->row($sql);

        if (empty($order)) {
            ECTouch::err()->add(L('order_exist'));
            return false;
        }

        // 如果用户ID大于0，检查订单是否属于该用户
        if ($user_id > 0 && $order['user_id'] != $user_id) {
            ECTouch::err()->add(L('no_priv'));

            return false;
        }

        // 订单状态只能是“未确认”或“已确认”
        if ($order['order_status'] != OS_UNCONFIRMED && $order['order_status'] != OS_CONFIRMED) {
            ECTouch::err()->add(L('current_os_not_unconfirmed'));

            return false;
        }

        //订单一旦确认，不允许用户取消
        if ($order['order_status'] == OS_CONFIRMED) {
            ECTouch::err()->add(L('current_os_already_confirmed'));

            return false;
        }

        // 发货状态只能是“未发货”
        if ($order['shipping_status'] != SS_UNSHIPPED) {
            ECTouch::err()->add(L('current_ss_not_cancel'));

            return false;
        }

        // 如果付款状态是“已付款”、“付款中”，不允许取消，要取消和商家联系
        if ($order['pay_status'] != PS_UNPAYED) {
            ECTouch::err()->add(L('current_ps_not_cancel'));

            return false;
        }

        //将用户订单设置为取消
        $sql = "UPDATE " . $this->pre . "order_info SET order_status = '" . OS_CANCELED . "' WHERE order_id = '$order_id'";
        if ($this->query($sql)) {
            /* 记录log */
            model('OrderBase')->order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, L('buyer_cancel'), 'buyer');
            /* 退货用户余额、积分、红包 */
            if ($order['user_id'] > 0 && $order['surplus'] > 0) {
                $change_desc = sprintf(L('return_surplus_on_cancel'), $order['order_sn']);
                model('ClipsBase')->log_account_change($order['user_id'], $order['surplus'], 0, 0, 0, $change_desc);
            }
            if ($order['user_id'] > 0 && $order['integral'] > 0) {
                $change_desc = sprintf(L('return_integral_on_cancel'), $order['order_sn']);
                model('ClipsBase')->log_account_change($order['user_id'], 0, 0, 0, $order['integral'], $change_desc);
            }
            if ($order['user_id'] > 0 && $order['bonus_id'] > 0) {
                model('Order')->change_user_bonus($order['bonus_id'], $order['order_id'], false);
            }

            /* 如果使用库存，且下订单时减库存，则增加库存 */
            if (C('use_storage') == '1' && C('stock_dec_time') == SDT_PLACE) {
                model('Order')->change_order_goods_storage($order['order_id'], false, 1);
            }

            /* 修改订单 */
            $arr = array(
                'bonus_id' => 0,
                'bonus' => 0,
                'integral' => 0,
                'integral_money' => 0,
                'surplus' => 0
            );
            model('Users')->update_order($order['order_id'], $arr);

            return true;
        } else {
            die(M()->errorMsg());
        }
    }

    /**
     * 确认一个用户订单
     *
     * @access  public
     * @param   int         $order_id       订单ID
     * @param   int         $user_id        用户ID
     *
     * @return  bool        $bool
     */
    function affirm_received($order_id, $user_id = 0) {
        /* 查询订单信息，检查状态 */
        $sql = "SELECT user_id, order_sn , order_status, shipping_status, pay_status FROM " . $this->pre . "order_info WHERE order_id = '$order_id'";

        $order = $this->row($sql);

        // 如果用户ID大于 0 。检查订单是否属于该用户
        if ($user_id > 0 && $order['user_id'] != $user_id) {
            ECTouch::err()->add(L('no_priv'));

            return false;
        }
        /* 检查订单 */ elseif ($order['shipping_status'] == SS_RECEIVED) {
            ECTouch::err()->add(L('order_already_received'));

            return false;
        } elseif ($order['shipping_status'] != SS_SHIPPED) {
            ECTouch::err()->add(L('order_invalid'));

            return false;
        }
        /* 修改订单发货状态为“确认收货” */ else {
            $sql = "UPDATE " . $this->pre . "order_info SET shipping_status = '" . SS_RECEIVED . "' WHERE order_id = '$order_id'";
            if ($this->query($sql)) {
                /* 记录日志 */
                model('OrderBase')->order_action($order['order_sn'], $order['order_status'], SS_RECEIVED, $order['pay_status'], '', L('buyer'));

                return true;
            } else {
                die(M()->errorMsg());
            }
        }
    }

    /**
     * 保存用户的收货人信息
     * 如果收货人信息中的 id 为 0 则新增一个收货人信息
     *
     * @access  public
     * @param   array   $consignee
     * @param   boolean $default        是否将该收货人信息设置为默认收货人信息
     * @return  boolean
     */
    function save_consignee($consignee, $default = false) {
        if ($consignee['address_id'] > 0) {
            /* 修改地址 */
            $this->table = 'user_address';
            $data['address_id'] = $consignee['address_id'];
            $condition['address_id'] = $consignee['address_id'];
            $condition['user_id'] = $_SESSION['user_id'];
            $res = $this->update($condition, $consignee);
        } else {
            /* 添加地址 */
            $this->table = 'user_address';
            $res = $this->insert($consignee);
            $consignee['address_id'] = M()->insert_id();
        }

        if ($default) {
            /* 保存为用户的默认收货地址 */
            $sql = "UPDATE " . $this->pre .
                    "users SET address_id = '$consignee[address_id]' WHERE user_id = '$_SESSION[user_id]'";

            $res = $this->query($sql);
        }

        return $res !== false;
    }

    /**
     * 删除一个收货地址
     *
     * @access  public
     * @param   integer $id
     * @return  boolean
     */
    function drop_consignee($id) {
        $sql = "SELECT user_id FROM " . $this->pre . "user_address WHERE address_id = '$id'";
        $res = $this->row($sql);
        $uid = $res['user_id'];

        if ($uid != $_SESSION['user_id']) {
            return false;
        } else {
            $sql = "DELETE FROM " . $this->pre . "user_address WHERE address_id = '$id'";
            $res = $this->query($sql);
            return $res;
        }
    }

    /**
     *  添加或更新指定用户收货地址
     *
     * @access  public
     * @param   array       $address
     * @return  bool
     */
    function update_address($address) {
        $address_id = intval($address['address_id']);
        unset($address['address_id']);
        $this->table = 'user_address';
        if ($address_id > 0) {
            /* 更新指定记录 */
            $condition['address_id'] = $address_id;
            $condition['user_id'] = $address['user_id'];
            $this->update($condition, $address);
        } else {
            /* 插入一条新记录 */
            $this->insert($address);
            $address_id = M()->insert_id();
        }

        if (isset($address['defalut']) && $address['default'] > 0 && isset($address['user_id'])) {
            $sql = "UPDATE " . $this->pre .
                    "users SET address_id = '" . $address_id . "' " .
                    " WHERE user_id = '" . $address['user_id'] . "'";
            $this->query($sql);
        }

        return true;
    }

    /**
     *  获取指订单的详情
     *
     * @access  public
     * @param   int         $order_id       订单ID
     * @param   int         $user_id        用户ID
     *
     * @return   arr        $order          订单所有信息的数组
     */
    function get_order_detail($order_id, $user_id = 0) {

        $order_id = intval($order_id);
        if ($order_id <= 0) {
            ECTouch::err()->add(L('invalid_order_id'));

            return false;
        }
        $order = model('Order')->order_info($order_id);

        //检查订单是否属于该用户
        if ($user_id > 0 && $user_id != $order['user_id']) {
            ECTouch::err()->add(L('no_priv'));

            return false;
        }

        /* 对发货号处理 */
        if (!empty($order['invoice_no'])) {
            $sql = "SELECT shipping_code FROM " . $this->pre . "shipping WHERE shipping_id = '$order[shipping_id]'";
            $res = $this->row($sql);
            $shipping_code = $res['shipping_code'];
            $plugin = ADDONS_PATH . 'shipping/' . $shipping_code . '.php';
            if (file_exists($plugin)) {
                include_once($plugin);
                $shipping = new $shipping_code;
                $order_tracking = $shipping->query($order['invoice_no']);
                $order['order_tracking'] = ($order_tracking == $order['invoice_no']) ? 0:1;
            }
        }

        /* 只有未确认才允许用户修改订单地址 */
        if ($order['order_status'] == OS_UNCONFIRMED) {
            $order['allow_update_address'] = 1; //允许修改收货地址
        } else {
            $order['allow_update_address'] = 0;
        }

        /* 获取订单中实体商品数量 */
        $order['exist_real_goods'] = model('Order')->exist_real_goods($order_id);

        /* 如果是未付款状态，生成支付按钮 */
        if ($order['pay_status'] == PS_UNPAYED && ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED)) {
            /*
             * 在线支付按钮
             */
            //支付方式信息
            $payment_info = array();
            $payment_info = Model('Order')->payment_info($order['pay_id']);
            // 只保留显示手机版支付方式
            if(!file_exists(ROOT_PATH . 'plugins/payment/'.$payment_info['pay_code'].'.php')){
                $payment_info = false;
            }

            //无效支付方式
            if ($payment_info === false || substr($payment_info['pay_code'], 0 , 4) == 'pay_') {
                $order['pay_online'] = '';
            } else {
                //取得支付信息，生成支付代码
                $payment = unserialize_config($payment_info['pay_config']);

                //获取需要支付的log_id
                $order['log_id'] = model('ClipsBase')->get_paylog_id($order['order_id'], $pay_type = PAY_ORDER);
                $order['user_name'] = $_SESSION['user_name'];
                $order['pay_desc'] = $payment_info['pay_desc'];

                /* 调用相应的支付方式文件 */
                include_once(ROOT_PATH . 'plugins/payment/' . $payment_info['pay_code'] . '.php');

                /* 取得在线支付方式的支付按钮 */
                $pay_obj = new $payment_info['pay_code'];
                $order['pay_online'] = $pay_obj->get_code($order, $payment);
            }
        } else {
            $order['pay_online'] = '';
        }

        /* 无配送时的处理 */
        $order['shipping_id'] == -1 and $order['shipping_name'] = L('shipping_not_need');

        /* 其他信息初始化 */
        $order['how_oos_name'] = $order['how_oos'];
        $order['how_surplus_name'] = $order['how_surplus'];

        /* 虚拟商品付款后处理 */
        if ($order['pay_status'] != PS_UNPAYED) {
            /* 取得已发货的虚拟商品信息 */
            $virtual_goods = model('OrderBase')->get_virtual_goods($order_id, true);
            $virtual_card = array();
            foreach ($virtual_goods AS $code => $goods_list) {
                /* 只处理虚拟卡 */
                if ($code == 'virtual_card') {
                    foreach ($goods_list as $goods) {
                        if ($info = model('OrderBase')->virtual_card_result($order['order_sn'], $goods)) {
                            $virtual_card[] = array('goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => $info);
                        }
                    }
                }
                /* 处理超值礼包里面的虚拟卡 */
                if ($code == 'package_buy') {
                    foreach ($goods_list as $goods) {
                        $sql = 'SELECT g.goods_id FROM ' . $this->pre . 'package_goods AS pg, ' . $this->pre . 'goods AS g ' .
                                "WHERE pg.goods_id = g.goods_id AND pg.package_id = '" . $goods['goods_id'] . "' AND extension_code = 'virtual_card'";
                        $vcard_arr = $this->query($sql);

                        foreach ($vcard_arr AS $val) {
                            if ($info = model('OrderBase')->virtual_card_result($order['order_sn'], $val)) {
                                $virtual_card[] = array('goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => $info);
                            }
                        }
                    }
                }
            }
            $var_card = deleteRepeat($virtual_card);
            ECTouch::view()->assign('virtual_card', $var_card);
        }

        /* 确认时间 支付时间 发货时间 */
        if ($order['confirm_time'] > 0 && ($order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED || $order['order_status'] == OS_SPLITING_PART)) {
            $order['confirm_time'] = sprintf(L('confirm_time'), local_date(C('time_format'), $order['confirm_time']));
        } else {
            $order['confirm_time'] = '';
        }
        if ($order['pay_time'] > 0 && $order['pay_status'] != PS_UNPAYED) {
            $order['pay_time'] = sprintf(L('pay_time'), local_date(C('time_format'), $order['pay_time']));
        } else {
            $order['pay_time'] = '';
        }
        if ($order['shipping_time'] > 0 && in_array($order['shipping_status'], array(SS_SHIPPED, SS_RECEIVED))) {
            $order['shipping_time'] = sprintf(L('shipping_time'), local_date(C('time_format'), $order['shipping_time']));
        } else {
            $order['shipping_time'] = '';
        }

        return $order;
    }

    /**
     *  获取用户可以和并的订单数组
     *
     * @access  public
     * @param   int         $user_id        用户ID
     *
     * @return  array       $merge          可合并订单数组
     */
    function get_user_merge($user_id) {
        $sql = "SELECT order_sn FROM " . $this->pre .
                "order_info WHERE user_id  = '$user_id' " . order_query_sql('unprocessed') .
                "AND extension_code = '' " .
                " ORDER BY add_time DESC";
        $list = $this->query($sql);
        $merge = array();
        foreach ($list as $key => $value) {

            $merge[$value['order_sn']] = $value['order_sn'];
        }
        return $merge;
    }

    /**
     *  合并指定用户订单
     *
     * @access  public
     * @param   string      $from_order         合并的从订单号
     * @param   string      $to_order           合并的主订单号
     *
     * @return  boolen      $bool
     */
    function merge_user_order($from_order, $to_order, $user_id = 0) {
        if ($user_id > 0) {
            /* 检查订单是否属于指定用户 */
            if (strlen($to_order) > 0) {
                $sql = "SELECT user_id FROM " . $this->pre .
                        "order_info WHERE order_sn = '$to_order'";
                $res = $this->row($sql);
                $order_user = $res['user_id'];
                if ($order_user != $user_id) {
                    ECTouch::err()->add(L('no_priv'));
                }
            } else {
                ECTouch::err()->add(L('order_sn_empty'));
                return false;
            }
        }

        $result = model('Order')->merge_order($from_order, $to_order);
        if ($result === true) {
            return true;
        } else {
            ECTouch::err()->add($result);
            return false;
        }
    }

    /**
     *  将指定订单中的商品添加到购物车
     *
     * @access  public
     * @param   int         $order_id
     *
     * @return  mix         $message        成功返回true, 错误返回出错信息
     */
    function return_to_cart($order_id) {
        /* 初始化基本件数量 goods_id => goods_number */
        $basic_number = array();

        /* 查订单商品：不考虑赠品 */
        $sql = "SELECT goods_id, product_id,goods_number, goods_attr, parent_id, goods_attr_id" .
                " FROM " . $this->pre .
                "order_goods WHERE order_id = '$order_id' AND is_gift = 0 AND extension_code <> 'package_buy'" .
                " ORDER BY parent_id ASC";
        $res = $this->query($sql);

        $time = gmtime();
        foreach ($res as $row) {
            // 查该商品信息：是否删除、是否上架

            $sql = "SELECT goods_sn, goods_name, goods_number, market_price, " .
                    "IF(is_promote = 1 AND '$time' BETWEEN promote_start_date AND promote_end_date, promote_price, shop_price) AS goods_price," .
                    "is_real, extension_code, is_alone_sale, goods_type " .
                    "FROM " . $this->pre .
                    "goods WHERE goods_id = '$row[goods_id]' " .
                    " AND is_delete = 0 LIMIT 1";
            $goods = $this->row($sql);

            // 如果该商品不存在，处理下一个商品
            if (empty($goods)) {
                continue;
            }
            if ($row['product_id']) {
                $order_goods_product_id = $row['product_id'];
                $sql = "SELECT product_number from " . $this->pre . "products where product_id='$order_goods_product_id'";
                $res = $this->row($sql);
                $product_number = $res['product_number'];
            }
            // 如果使用库存，且库存不足，修改数量
            if (C('use_storage') == 1 && ($row['product_id'] ? ($product_number < $row['goods_number']) : ($goods['goods_number'] < $row['goods_number']))) {
                if ($goods['goods_number'] == 0 || $product_number === 0) {
                    // 如果库存为0，处理下一个商品
                    continue;
                } else {
                    if ($row['product_id']) {
                        $row['goods_number'] = $product_number;
                    } else {
                        // 库存不为0，修改数量
                        $row['goods_number'] = $goods['goods_number'];
                    }
                }
            }

            //检查商品价格是否有会员价格
            $sql = "SELECT goods_number FROM" . $this->pre . " " .
                    "cart WHERE session_id = '" . SESS_ID . "' " .
                    "AND goods_id = '" . $row['goods_id'] . "' " .
                    "AND rec_type = '" . CART_GENERAL_GOODS . "' LIMIT 1";
            $res = $this->row($sql);
            $temp_number = $res['goods_number'];
            $row['goods_number'] += $temp_number;

            $attr_array = empty($row['goods_attr_id']) ? array() : explode(',', $row['goods_attr_id']);
            $goods['goods_price'] = model('GoodsBase')->get_final_price($row['goods_id'], $row['goods_number'], true, $attr_array);

            // 要返回购物车的商品
            $return_goods = array(
                'goods_id' => $row['goods_id'],
                'goods_sn' => addslashes($goods['goods_sn']),
                'goods_name' => addslashes($goods['goods_name']),
                'market_price' => $goods['market_price'],
                'goods_price' => $goods['goods_price'],
                'goods_number' => $row['goods_number'],
                'goods_attr' => empty($row['goods_attr']) ? '' : addslashes($row['goods_attr']),
                'goods_attr_id' => empty($row['goods_attr_id']) ? '' : addslashes($row['goods_attr_id']),
                'is_real' => $goods['is_real'],
                'extension_code' => addslashes($goods['extension_code']),
                'parent_id' => '0',
                'is_gift' => '0',
                'rec_type' => CART_GENERAL_GOODS
            );

            // 如果是配件
            if ($row['parent_id'] > 0) {
                // 查询基本件信息：是否删除、是否上架、能否作为普通商品销售
                $sql = "SELECT goods_id " .
                        "FROM " . $this->pre .
                        "goods WHERE goods_id = '$row[parent_id]' " .
                        " AND is_delete = 0 AND is_on_sale = 1 AND is_alone_sale = 1 LIMIT 1";
                $parent = $this->row($sql);
                if ($parent) {
                    // 如果基本件存在，查询组合关系是否存在
                    $sql = "SELECT goods_price " .
                            "FROM " . $this->pre .
                            "group_goods WHERE parent_id = '$row[parent_id]' " .
                            " AND goods_id = '$row[goods_id]' LIMIT 1";
                    $fitting_price = $this->row($sql);
                    if ($fitting_price['goods_price']) {
                        // 如果组合关系存在，取配件价格，取基本件数量，改parent_id
                        $return_goods['parent_id'] = $row['parent_id'];
                        $return_goods['goods_price'] = $fitting_price['goods_price'];
                        $return_goods['goods_number'] = $basic_number[$row['parent_id']];
                    }
                }
            } else {
                // 保存基本件数量
                $basic_number[$row['goods_id']] = $row['goods_number'];
            }

            // 返回购物车：看有没有相同商品
            $sql = "SELECT goods_id " .
                    "FROM " . $this->pre .
                    "cart WHERE session_id = '" . SESS_ID . "' " .
                    " AND goods_id = '$return_goods[goods_id]' " .
                    " AND goods_attr = '$return_goods[goods_attr]' " .
                    " AND parent_id = '$return_goods[parent_id]' " .
                    " AND is_gift = 0 " .
                    " AND rec_type = '" . CART_GENERAL_GOODS . "'";
            $res = $this->row($sql);
            $cart_goods = $res['goods_id'];
            if (empty($cart_goods)) {
                // 没有相同商品，插入
                $return_goods['session_id'] = SESS_ID;
                $return_goods['user_id'] = $_SESSION['user_id'];
                $this->table = 'cart';
                $this->insert($return_goods);
            } else {
                // 有相同商品，修改数量
                $sql = "UPDATE " . $this->pre . "cart SET " .
                        "goods_number = '" . $return_goods['goods_number'] . "' " .
                        ",goods_price = '" . $return_goods['goods_price'] . "' " .
                        "WHERE session_id = '" . SESS_ID . "' " .
                        "AND goods_id = '" . $return_goods['goods_id'] . "' " .
                        "AND rec_type = '" . CART_GENERAL_GOODS . "' LIMIT 1";
                $this->query($sql);
            }
        }


        // 清空购物车的赠品
        $sql = "DELETE FROM " . $this->pre .
                "cart WHERE session_id = '" . SESS_ID . "' AND is_gift = 1";
        $this->query($sql);

        return true;
    }

    /**
     *  保存用户收货地址
     *
     * @access  public
     * @param   array   $address        array_keys(consignee string, email string, address string, zipcode string, tel string, mobile stirng, sign_building string, best_time string, order_id int)
     * @param   int     $user_id        用户ID
     *
     * @return  boolen  $bool
     */
    function save_order_address($address, $user_id) {
        ECTouch::err()->clean();
        /* 数据验证 */
        empty($address['consignee']) and ECTouch::err()->add(L('consigness_empty'));
        empty($address['address']) and ECTouch::err()->add(L('address_empty'));
        $address['order_id'] == 0 and ECTouch::err()->add(L('order_id_empty'));
        if (empty($address['email'])) {
            ECTouch::err()->add($GLOBALS['email_empty']);
        } else {
            if (!is_email($address['email'])) {
                ECTouch::err()->add(sprintf(L('email_invalid'), $address['email']));
            }
        }
        if (ECTouch::err()->error_no > 0) {
            return false;
        }

        /* 检查订单状态 */
        $sql = "SELECT user_id, order_status FROM " . $this->pre . "order_info WHERE order_id = '" . $address['order_id'] . "'";
        $row = $this->row($sql);
        if ($row) {
            if ($user_id > 0 && $user_id != $row['user_id']) {
                ECTouch::err()->add(L('no_priv'));
                return false;
            }
            if ($row['order_status'] != OS_UNCONFIRMED) {
                ECTouch::err()->add(L('require_unconfirmed'));
                return false;
            }
            $this->table = 'order_info';
            $condition['order_id'] = $address['order_id'];
            $this->update($condition, $address);
            return true;
        } else {
            /* 订单不存在 */
            ECTouch::err()->add(L('order_exist'));
            return false;
        }
    }

    /**
     *
     * @access  public
     * @param   int         $user_id         用户ID
     * @param   int         $num             列表显示条数
     * @param   int         $start           显示起始位置
     *
     * @return  array       $arr             红保列表
     */
    function get_user_bouns_list($user_id, $num = 10, $start = 0) {
        $sql = "SELECT u.bonus_sn, u.order_id, b.type_name, b.type_money, b.min_goods_amount, b.use_start_date, b.use_end_date " .
                " FROM " . $this->pre . "user_bonus AS u ," .
                $this->pre . "bonus_type AS b" .
                " WHERE u.bonus_type_id = b.type_id AND u.user_id = '" . $user_id . "' ORDER BY bonus_id DESC LIMIT " . $start . ',' . $num;
        $res = $this->query($sql);
        $arr = array();

        $day = getdate();
        $cur_date = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
        foreach ($res as $row) {
            /* 先判断是否被使用，然后判断是否开始或过期 */
            if (empty($row['order_id'])) {
                /* 没有被使用 */
                if ($row['use_start_date'] > $cur_date) {
                    $row['status'] = L('not_start');
                } else if ($row['use_end_date'] < $cur_date) {
                    $row['status'] = L('overdue');
                } else {
                    $row['status'] = L('not_use');
                }
            } else {
                $url = url('user/order_detail', array('order_id'=>$row['order_id']));
                $row['status'] = '<a href="'.$url.'" >' . L('had_use') . '</a>';
            }

            $row['use_startdate'] = local_date(C('date_format'), $row['use_start_date']);
            $row['use_enddate'] = local_date(C('date_format'), $row['use_end_date']);

            $arr[] = $row;
        }
        return $arr;
    }

    /**
     * 通过判断is_feed 向UCenter提交Feed
     *
     * @access public
     * @param  integer $value_id  $order_id or $comment_id
     * @param  interger $feed_type BUY_GOODS or COMMENT_GOODS
     *
     * @return void
     */
    function add_feed($id, $feed_type) {
        $feed = array();
        if ($feed_type == BUY_GOODS) {
            if (empty($id)) {
                return;
            }
            $id = intval($id);
            $sql = "SELECT g.goods_id, g.goods_name, g.goods_sn, g.goods_desc, g.goods_thumb, o.goods_price FROM " . $this->pre . "order_goods AS o, " . $this->pre . "goods AS g WHERE o.order_id='{$id}' AND o.goods_id=g.goods_id";
            $order_res = $this->query($sql);
            foreach ($order_res as $goods_data) {
                if (!empty($goods_data['goods_thumb'])) {
                    $url = __URL__ . $goods_data['goods_thumb']; //ECTouch::ecs()->url() . $goods_data['goods_thumb'];
                } else {
                    $url = __URL__ . C('no_picture'); //ECTouch::ecs()->url() . C('no_picture');
                }
                $link = __HOST__ . url('goods/index', array('id' => $goods_data["goods_id"])); //ECTouch::ecs()->url() . "goods.php?id=" . $goods_data["goods_id"];

                $feed['icon'] = "goods";
                $feed['title_template'] = '<b>{username} ' . L('feed_user_buy') . ' {goods_name}</b>';
                $feed['title_data'] = array('username' => $_SESSION['user_name'], 'goods_name' => $goods_data['goods_name']);
                $feed['body_template'] = '{goods_name}  ' . L('feed_goods_price') . ':{goods_price}  ' . L('feed_goods_desc') . ':{goods_desc}';
                $feed['body_data'] = array('goods_name' => $goods_data['goods_name'], 'goods_price' => $goods_data['goods_price'], 'goods_desc' => sub_str(strip_tags($goods_data['goods_desc']), 150, true));
                $feed['images'][] = array('url' => $url,
                    'link' => $link);
                uc_call("uc_feed_add", array($feed['icon'], $_SESSION['user_id'], $_SESSION['user_name'], $feed['title_template'], $feed['title_data'], $feed ['body_template'], $feed['body_data'], '', '', $feed['images']));
            }
        }
        return;
    }

    /**
     * 指定默认配送地址
     *
     */
    function save_consignee_default($address_id) {
        /* 保存为用户的默认收货地址 */
        $sql = "UPDATE " . $this->pre .
                "users SET address_id = '$address_id' WHERE user_id = '$_SESSION[user_id]'";

        $res = $this->query($sql);

        return $res !== false;
    }

    /**
     * 根据商品id获取购物车中此id的数量
     */
    function get_goods_number($goods_id) {
        // 查询
        $sql = "SELECT IFNULL(SUM(goods_number), 0) as number " .
                " FROM " . $this->pre .
                "cart WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "' AND goods_id = " . $goods_id;
        $res = $this->row($sql);
        return $res['number'];
    }

    /**
     *  获取用户信息数组
     *
     * @access  public
     * @param
     *
     * @return array        $user       用户信息数组
     */
    function get_user_info($id = 0) {
        if ($id == 0) {
            $id = $_SESSION['user_id'];
        }
        $time = date('Y-m-d');
        $sql = 'SELECT u.user_id, u.email, u.user_name, u.user_money, u.pay_points' .
                ' FROM ' . $this->pre . 'users AS u ' .
                " WHERE u.user_id = '$id'";
        $user = $this->row($sql);
        $bonus = model('ClipsBase')->get_user_bonus($id);

        $user['username'] = $user['user_name'];
        $user['user_points'] = $user['pay_points'] . C('integral_name');
        $user['user_money'] = price_format($user['user_money'], false);
        $user['user_bonus'] = price_format($bonus['bonus_value'], false);

        return $user;
    }

    /**
     * 获得订单中的费用信息
     *
     * @access  public
     * @param   array   $order
     * @param   array   $goods
     * @param   array   $consignee
     * @param   bool    $is_gb_deposit  是否团购保证金（如果是，应付款金额只计算商品总额和支付费用，可以获得的积分取 $gift_integral）
     * @return  array
     */
    function order_fee($order, $goods, $consignee) {
        /* 初始化订单的扩展code */
        if (!isset($order['extension_code'])) {
            $order['extension_code'] = '';
        }

        if ($order['extension_code'] == 'group_buy') {
            $group_buy = model('GroupBuyBase')->group_buy_info($order['extension_id']);
        }

        $total = array('real_goods_count' => 0,
            'gift_amount' => 0,
            'goods_price' => 0,
            'market_price' => 0,
            'discount' => 0,
            'pack_fee' => 0,
            'card_fee' => 0,
            'shipping_fee' => 0,
            'shipping_insure' => 0,
            'integral_money' => 0,
            'bonus' => 0,
            'surplus' => 0,
            'cod_fee' => 0,
            'pay_fee' => 0,
            'tax' => 0);
        $weight = 0;

        /* 商品总价 */
        foreach ($goods AS $val) {
            /* 统计实体商品的个数 */
            if ($val['is_real']) {
                $total['real_goods_count']++;
            }

            $total['goods_price'] += $val['goods_price'] * $val['goods_number'];
            $total['market_price'] += $val['market_price'] * $val['goods_number'];
        }

        $total['saving'] = $total['market_price'] - $total['goods_price'];
        $total['save_rate'] = $total['market_price'] ? round($total['saving'] * 100 / $total['market_price']) . '%' : 0;

        $total['goods_price_formated'] = price_format($total['goods_price'], false);
        $total['market_price_formated'] = price_format($total['market_price'], false);
        $total['saving_formated'] = price_format($total['saving'], false);

        /* 折扣 */
        if ($order['extension_code'] != 'group_buy') {
            $discount = model('Order')->compute_discount();
            $total['discount'] = $discount['discount'];
            if ($total['discount'] > $total['goods_price']) {
                $total['discount'] = $total['goods_price'];
            }
        }
        $total['discount_formated'] = price_format($total['discount'], false);

        /* 税额 */
        if (!empty($order['need_inv']) && $order['inv_type'] != '') {
            /* 查税率 */
            $rate = 0;
            $invoice_type = C('invoice_type');
            foreach ($invoice_type['type'] as $key => $type) {
                if ($type == $order['inv_type']) {
                    $rate = floatval($invoice_type['rate'][$key]) / 100;
                    break;
                }
            }
            if ($rate > 0) {
                $total['tax'] = $rate * $total['goods_price'];
            }
        }
        $total['tax_formated'] = price_format($total['tax'], false);

        /* 包装费用 */
        if (!empty($order['pack_id'])) {
            $total['pack_fee'] = pack_fee($order['pack_id'], $total['goods_price']);
        }
        $total['pack_fee_formated'] = price_format($total['pack_fee'], false);

        /* 贺卡费用 */
        if (!empty($order['card_id'])) {
            $total['card_fee'] = card_fee($order['card_id'], $total['goods_price']);
        }
        $total['card_fee_formated'] = price_format($total['card_fee'], false);

        /* 红包 */

        if (!empty($order['bonus_id'])) {
            $bonus = model('Order')->bonus_info($order['bonus_id']);
            $total['bonus'] = $bonus['type_money'];
        }
        $total['bonus_formated'] = price_format($total['bonus'], false);

        /* 线下红包 */
        if (!empty($order['bonus_kill'])) {
            $bonus = model('Order')->bonus_info(0, $order['bonus_kill']);
            $total['bonus_kill'] = $order['bonus_kill'];
            $total['bonus_kill_formated'] = price_format($total['bonus_kill'], false);
        }



        /* 配送费用 */
        $shipping_cod_fee = NULL;

        if ($order['shipping_id'] > 0 && $total['real_goods_count'] > 0) {
            $region['country'] = $consignee['country'];
            $region['province'] = $consignee['province'];
            $region['city'] = $consignee['city'];
            $region['district'] = $consignee['district'];
            $shipping_info = model('Shipping')->shipping_area_info($order['shipping_id'], $region);
            $old_price = $total['goods_price'];
            if (!empty($shipping_info)) {
                if ($order['extension_code'] == 'group_buy') {
                    $weight_price = model('Order')->cart_weight_price(CART_GROUP_BUY_GOODS);
                } else {
                    $weight_price = model('Order')->cart_weight_price();
                }

                // 查看购物车中是否全为免运费商品，若是则把运费赋为零
                $sql = 'SELECT count(*) as count FROM ' . $this->pre . "cart WHERE  `session_id` = '" . SESS_ID . "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0";
                $res = $this->row($sql);
                $shipping_count = $res['count'];

                $total['shipping_fee'] = ($shipping_count == 0 AND $weight_price['free_shipping'] == 1) ? 0 : shipping_fee($shipping_info['shipping_code'], $shipping_info['configure'], $weight_price['weight'], $old_price = $group_buy['cur_price'] > 0 ? $group_buy['cur_price'] : $old_price, $weight_price['number']);
                if (!empty($order['need_insure']) && $shipping_info['insure'] > 0) {
                    $total['shipping_insure'] = shipping_insure_fee($shipping_info['shipping_code'], $total['goods_price'], $shipping_info['insure']);
                } else {
                    $total['shipping_insure'] = 0;
                }

                if ($shipping_info['support_cod']) {
                    $shipping_cod_fee = $shipping_info['pay_fee'];
                }
            }
        }

        $total['shipping_fee_formated'] = price_format($total['shipping_fee'], false);
        $total['shipping_insure_formated'] = price_format($total['shipping_insure'], false);

        // 购物车中的商品能享受红包支付的总额
        $bonus_amount = model('Order')->compute_discount_amount();
        // 红包和积分最多能支付的金额为商品总额
        $max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;

        /* 计算订单总额 */
        if ($order['extension_code'] == 'group_buy' && $group_buy['deposit'] > 0) {
            $total['amount'] = $total['goods_price'] + $total['shipping_fee'];
        } else {
            $total['amount'] = $total['goods_price'] - $total['discount'] + $total['tax'] + $total['pack_fee'] + $total['card_fee'] +
                    $total['shipping_fee'] + $total['shipping_insure'] + $total['cod_fee'];

            // 减去红包金额
            $use_bonus = min($total['bonus'], $max_amount); // 实际减去的红包金额
            if (isset($total['bonus_kill'])) {
                $use_bonus_kill = min($total['bonus_kill'], $max_amount);
                $total['amount'] -= $price = number_format($total['bonus_kill'], 2, '.', ''); // 还需要支付的订单金额
            }

            $total['bonus'] = $use_bonus;
            $total['bonus_formated'] = price_format($total['bonus'], false);

            $total['amount'] -= $use_bonus; // 还需要支付的订单金额
            $max_amount -= $use_bonus; // 积分最多还能支付的金额
        }

        /* 余额 */
        $order['surplus'] = $order['surplus'] > 0 ? $order['surplus'] : 0;
        if ($total['amount'] > 0) {
            if (isset($order['surplus']) && $order['surplus'] > $total['amount']) {
                $order['surplus'] = $total['amount'];
                $total['amount'] = 0;
            } else {
                $total['amount'] -= floatval($order['surplus']);
            }
        } else {
            $order['surplus'] = 0;
            $total['amount'] = 0;
        }
        $total['surplus'] = $order['surplus'];
        $total['surplus_formated'] = price_format($order['surplus'], false);

        /* 积分 */
        $order['integral'] = $order['integral'] > 0 ? $order['integral'] : 0;
        if ($total['amount'] > 0 && $max_amount > 0 && $order['integral'] > 0) {
            $integral_money = value_of_integral($order['integral']);

            // 使用积分支付
            $use_integral = min($total['amount'], $max_amount, $integral_money); // 实际使用积分支付的金额
            $total['amount'] -= $use_integral;
            $total['integral_money'] = $use_integral;
            $order['integral'] = integral_of_value($use_integral);
        } else {
            $total['integral_money'] = 0;
            $order['integral'] = 0;
        }
        $total['integral'] = $order['integral'];
        $total['integral_formated'] = price_format($total['integral_money'], false);

        /* 保存订单信息 */
        $_SESSION['flow_order'] = $order;

        $se_flow_type = isset($_SESSION['flow_type']) ? $_SESSION['flow_type'] : '';

        /* 支付费用 */
        if (!empty($order['pay_id']) && ($total['real_goods_count'] > 0 || $se_flow_type != CART_EXCHANGE_GOODS)) {
            $total['pay_fee'] = pay_fee($order['pay_id'], $total['amount'], $shipping_cod_fee);
        }

        $total['pay_fee_formated'] = price_format($total['pay_fee'], false);

        $total['amount'] += $total['pay_fee']; // 订单总额累加上支付费用
        $total['amount_formated'] = price_format($total['amount'], false);

        /* 取得可以得到的积分和红包 */
        if ($order['extension_code'] == 'group_buy') {
            $total['will_get_integral'] = $group_buy['gift_integral'];
        } elseif ($order['extension_code'] == 'exchange_goods') {
            $total['will_get_integral'] = 0;
        } else {
            $total['will_get_integral'] = model('Order')->get_give_integral($goods);
        }
        $total['will_get_bonus'] = $order['extension_code'] == 'exchange_goods' ? 0 : price_format(model('Order')->get_total_bonus(), false);
        $total['formated_goods_price'] = price_format($total['goods_price'], false);
        $total['formated_market_price'] = price_format($total['market_price'], false);
        $total['formated_saving'] = price_format($total['saving'], false);

        if ($order['extension_code'] == 'exchange_goods') {
            $sql = 'SELECT SUM(eg.exchange_integral) ' .
                    'as sum FROM ' . $this->pre . 'cart AS c,' . $this->pre . 'exchange_goods AS eg ' .
                    "WHERE c.goods_id = eg.goods_id AND c.session_id= '" . SESS_ID . "' " .
                    "  AND c.rec_type = '" . CART_EXCHANGE_GOODS . "' " .
                    '  AND c.is_gift = 0 AND c.goods_id > 0 ' .
                    'GROUP BY eg.goods_id';
            $res = $this->row($sql);
            $exchange_integral = $res['sum'];
            $total['exchange_integral'] = $exchange_integral;
        }

        return $total;
    }

    /**
     * 修改订单
     * @param   int     $order_id   订单id
     * @param   array   $order      key => value
     * @return  bool
     */
    function update_order($order_id, $order) {
        $this->table = 'order_info';
        $condition['order_id'] = $order_id;

        $res = $this->query('DESC ' . $this->pre . $this->table);

        while ($row = mysqli_fetch_row($res)) {
            $field_names[] = $row[0];
        }
        foreach ($field_names as $value) {
            if (array_key_exists($value, $order) == true) {
                $order_info[$value] = $order[$value];
            }
        }
        return $this->update($condition, $order_info);
    }

    /**
     * 重新计算购物车中的商品价格：目的是当用户登录时享受会员价格，当用户退出登录时不享受会员价格
     * 如果商品有促销，价格不变
     *
     * @access  public
     * @return  void
     */
    function recalculate_price() {
        /* 取得有可能改变价格的商品：除配件和赠品之外的商品 */
        $sql = 'SELECT c.rec_id, c.goods_id, c.goods_attr_id, g.promote_price, g.promote_start_date, c.goods_number,' .
                "g.promote_end_date, IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS member_price " .
                'FROM ' . $this->pre . 'cart AS c ' .
                'LEFT JOIN ' . $this->pre . 'goods AS g ON g.goods_id = c.goods_id ' .
                "LEFT JOIN " . $this->pre . "member_price AS mp " .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '" . $_SESSION['user_rank'] . "' " .
                "WHERE session_id = '" . SESS_ID . "' AND c.parent_id = 0 AND c.is_gift = 0 AND c.goods_id > 0 " .
                "AND c.rec_type = '" . CART_GENERAL_GOODS . "' AND c.extension_code <> 'package_buy'";

        $res = $this->query($sql);

        foreach ($res AS $row) {
            $attr_id = empty($row['goods_attr_id']) ? array() : explode(',', $row['goods_attr_id']);


            $goods_price = model('GoodsBase')->get_final_price($row['goods_id'], $row['goods_number'], true, $attr_id);


            $goods_sql = "UPDATE " . $this->pre . "cart SET goods_price = '$goods_price' " .
                    "WHERE goods_id = '" . $row['goods_id'] . "' AND session_id = '" . SESS_ID . "' AND rec_id = '" . $row['rec_id'] . "'";

            $this->query($goods_sql);
        }

        /* 删除赠品，重新选择 */
        $this->query('DELETE FROM ' . $this->pre .
                "cart WHERE session_id = '" . SESS_ID . "' AND is_gift > 0");
    }

    /**
     * 获取推荐uid
     *
     * @access  public
     * @param   void
     *
     * @return int
     * @author xuanyan
     * */
    // function get_affiliate() {
    //     if (!empty($_COOKIE['ecshop_affiliate_uid'])) {
    //         $uid = intval($_COOKIE['ecshop_affiliate_uid']);
    //         if ($this->row('SELECT user_id FROM ' . $this->pre . "users WHERE user_id = '$uid'")) {
    //             return $uid;
    //         } else {
    //             setcookie('ecshop_affiliate_uid', '', 1);
    //         }
    //     }
    //     elseif($_SESSION['user_id'] !== 0){
    //         //推荐 by ecmoban
    //         $reg_info = $this->model->table('users')->field('reg_time, parent_id')->where('user_id = '.$_SESSION['user_id'])->find();
    //         //推荐信息
    //         $config = unserialize(C('affiliate'));
    //         if (!empty($config['config']['expire'])) {
    //             if ($config['config']['expire_unit'] == 'hour') {
    //                 $c = 1;
    //             } elseif ($config['config']['expire_unit'] == 'day') {
    //                 $c = 24;
    //             } elseif ($config['config']['expire_unit'] == 'week') {
    //                 $c = 24 * 7;
    //             } else {
    //                 $c = 1;
    //             }
    //             //有效时间
    //             $eff_time = 3600 * $config['config']['expire'] * $c;
    //             //有效时间内
    //             if(gmtime() - $reg_info['reg_time'] <= $eff_time){
    //                 return $reg_info['parent_id'];
    //             }
    //         }
    //     }

    //     return 0;
    // }
    /**
     * 检查是否为第三方用户
     * @param type $user_id
     * @return type
     */
    function is_third_user($user_id) {
        $sql = 'SELECT count(*) as count FROM ' . $this->pre . 'touch_user_info t LEFT JOIN ' . $this->pre .
                'users u ON t.user_id = u.user_id  WHERE u.user_id = "' . $user_id . '" ';
        $res = $this->row($sql);
        return $res['count'];
    }
    /**
     * 检查该用户是否启动过第三方登录
     * @param type $aite_id
     * @return type
     */
    function get_one_user($aite_id) {
        // pc兼容模式，安装pc端插件之后移除注释
        /**
        $sql = 'SELECT user_name FROM ' . $this->pre . 'users WHERE aite_id = "' . $aite_id . '" ';
        $res = $this->row($sql);
        if($res){
            return $res['user_name'];
        }
        */
        // 手机独立模式
        $sql = 'SELECT u.user_name, u.user_id FROM ' . $this->pre . 'users u LEFT JOIN ' . $this->pre .
                'touch_user_info t ON t.user_id = u.user_id WHERE t.aite_id = "' . $aite_id . '" ';
        return $this->row($sql);
    }

    /**
     * 插入第三方登录信息到数据库
     * @param type $info
     * @return boolean
     */
    function third_reg($info) {
        $username = $info['user_name'];
        $password = time();
        $email = $info['email'];
        if ($this->register($username, $password, $email) !== false) {
            $uid = $_SESSION['user_id'];
            // 更新附表
            $this->table = "touch_user_info";
            $touch_data['user_id'] = $uid;
            //$touch_data['user_id'] = $_SESSION['user_id'];
            $touch_data['aite_id'] = $info['aite_id'];
            $this->insert($touch_data);
            // 兼容pc端登录
            // $this->model->table('users')->data($touch_data)->where(array('user_id'=>$uid))->update();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 查询社会化登录用户信息
     * @param string $unionid
     * @return array
     */
    function get_connect_user($unionid) {
        $sql = "SELECT u.user_name, u.user_id, u.parent_id FROM {pre}users u, {pre}connect_user cu WHERE u.user_id = cu.user_id AND cu.open_id = '" . $unionid. "' ";
        return $this->row($sql);
    }

    /**
     * 更新社会化登录用户信息
     * @param   $res, $type:qq,weibo,wechat
     * @return
     */
    function update_connnect_user($res, $type = '')
    {
        // 组合数据
        $profile = array(
            'nickname' => $res['nickname'],
            'sex' => $res['sex'],
            'province' => $res['province'],
            'city' => $res['city'],
            'country' => $res['country'],
            'headimgurl' => $res['headimgurl'],
        );
        $type = $type == 'weixin' ? 'wechat' : $type; // 兼容统一命名wechat
        $data = array(
            'connect_code' => 'sns_' . $type,
            'user_id' => $res['user_id'],
            'open_id' => $res['unionid'],
            'profile' => serialize($profile)
        );
        if($res['user_id'] > 0 && $res['unionid']){
            // 查询
            $connect_userinfo = $this->get_connect_user($res['unionid']);
            if (empty($connect_userinfo)) {
                // 新增记录
                $data['create_at'] = gmtime();
                $this->model->table('connect_user')->data($data)->insert();
            } else {
                // 更新记录
                $this->model->table('connect_user')->data($data)->where(array('open_id' => $res['unionid']))->update();
            }
        }
    }

    /**
     * 更新微信用户信息
     * @param array    $info 微信用户信息
     * @param string   $wechat_id  公众号ID
     * @return
     */
    function update_wechat_user($info, $wechat_id = '')
    {
        //公众号id
        $wechat = $this->model->table('wechat')->field('id')->where(array('type' => 2, 'status' => 1, 'default_wx' => 1))->find();
        $wechat_id = !empty($wechat_id) ? $wechat_id : $wechat['id'];
        // 组合数据
        $data = array(
            'wechat_id' => $wechat_id,
            'openid' => $info['openid'],
            'nickname' => !empty($info['nickname']) ? $info['nickname'] : '',
            'sex' => !empty($info['sex']) ? $info['sex'] : 0,
            'language' => !empty($info['language']) ? $info['language'] : '',
            'city' => !empty($info['city']) ? $info['city'] : '',
            'province' => !empty($info['province']) ? $info['province'] : '',
            'country' => !empty($info['country']) ? $info['country'] : '',
            'headimgurl' => !empty($info['headimgurl']) ? $info['headimgurl'] : '',
            'unionid' => $info['unionid'],
        );
        if(!empty($info['user_id'])) { $data['ect_uid'] = $info['user_id'];}

        // unionid 微信开放平台唯一标识
        if(!empty($info['unionid'])){
            // 查询
            $where = array('unionid' => $info['unionid'], 'wechat_id' => $wechat_id);
            $result = $this->model->table('wechat_user')->field('openid, unionid')->where($where)->find();
            if (empty($result)) {
                // 新增记录
                $this->model->table('wechat_user')->data($data)->insert();
            } else {
                // 更新记录
                $this->model->table('wechat_user')->data($data)->where($where)->update();
            }
        }
    }

    /**
     * 检查用户名是否重名 by leah
     * @param type $user_name
     * @return type
     */
    function check_user_name($user_name) {
        $this->table = 'users';
        $condition['user_name'] = $user_name;
        return $this->count($condition);
    }

	 /**
     * 获取订单商品数量
     * @return type
     */
    function get_order_goods_count($order_id) {

        $sql = "SELECT  COUNT(*) as count " .
            "FROM " . $this->pre . "order_goods AS o " .
            "LEFT JOIN " . $this->pre . "products AS p ON o.product_id = p.product_id " .
            "LEFT JOIN " . $this->pre . "goods AS g ON o.goods_id = g.goods_id " .
            "WHERE o.order_id = '$order_id' ";
        $res = $this->row($sql);
        return $res['count'];
    }

    /**
     * 查询会员账户明细
     * @access  public
     * @param   int     $user_id    会员ID
     * @param   int     $num        每页显示数量
     * @param   int     $start      开始显示的条数
     * @return  array
     */
    public function get_account_detail($user_id, $num, $start) {

        // 获取余额记录
        $account_log = array();

        $sql = 'SELECT * FROM ' . $this->pre . "account_log WHERE user_id = " . $user_id . ' AND user_money <> 0' .
        " ORDER BY log_id DESC limit " . $start . ',' . $num;
        $res = $this->query($sql);

        if (empty($res)) {
            return array();
            exit;
        }

        foreach ($res as $k => $v) {
            $res[$k]['change_time'] = local_date(C('date_format'), $v['change_time']);
            $res[$k]['type'] = $v['user_money'] > 0 ? L('account_inc') : L('account_dec');
            $res[$k]['user_money'] = price_format(abs($v['user_money']), false);
            $res[$k]['frozen_money'] = price_format(abs($v['frozen_money']), false);
            $res[$k]['rank_points'] = abs($v['rank_points']);
            $res[$k]['pay_points'] = abs($v['pay_points']);
            $res[$k]['short_change_desc'] = sub_str($v['change_desc'], 60);
            $res[$k]['amount'] = $v['user_money'];
        }

        return $res;


    }


    // 退换货 start
    /**
     * 获取订单所对应的服务类型数组
     * @param $order
     * @return array
     */
    public function get_service_opt($order) {

        $service_return = $this->model->table('service_type')->where("service_type = " . ST_RETURN_GOODS)->find();
        $service_exchange = $this->model->table('service_type')->where("service_type = " . ST_EXCHANGE)->find();

        $time = gmtime();
        $type_list = array();
        if ($order['pay_status'] == PS_PAYED) {
            //订单已付款
            if ($order['order_status'] == OS_SPLITED) {
                //已分单
                if ($order['shipping_status'] == SS_SHIPPED) {
                    //已发货 退款
                    $action = $this->model->table('order_action')->field('log_time')->where(array('shipping_status' => SS_SHIPPED, 'order_id' => $order['order_id']))->find(); //获取发货时间
                    /* 退货退款 现在时间-发货时时间 得到天数 */
                    $days = (($time - $action['log_time']) / 3600 / 24);
                    if ($days <= $service_return['unreceived_days']) {
                        $type_list[] = ST_RETURN_GOODS;
                    } else {
                        show_message(L('time_out'));
                    }
                    if ($days <= $service_exchange['unreceived_days']) {
                        $type_list[] = ST_EXCHANGE;
                    } else {
                        show_message(L('time_out'));
                    }
                } elseif ($order['shipping_status'] == SS_RECEIVED) {
                    //已收货 退货换货，退款
                    $action = $this->model->table('order_action')->field('log_time')->where(array('shipping_status' => SS_RECEIVED, 'order_id' => $order['order_id']))->find(); //获取发货时间                    
                    /* 退货退款 现在时间-发货时时间 得到天数 */
                    $days = (($time - $action['log_time']) / 3600 / 24);

                    if ($days <= $service_return['received_days']) {
                        $type_list[] = ST_RETURN_GOODS;
                    } else {
                        show_message(L('time_out'));
                    }
                    if ($days <= $service_exchange['received_days']) {
                        $type_list[] = ST_EXCHANGE;
                    } else {
                        show_message(L('time_out'));
                    }
                } else {
                    //其他
                }
            }
        }
        return $type_list;
    }

    /** 获取所有服务类型列表
     * @param $order_id
     * @param $rec_id
     * @param $service_type
     * @return array
     */
    public function get_service_type_list($order_id, $rec_id, $service_type) {
        $where = " service_type in(" . implode(',', $service_type) . ")";
        $sql = 'SELECT service_id, service_name, service_desc, service_type FROM ' . $this->pre . 'service_type' . ' WHERE  is_show = 1 AND' . $where . ' ORDER BY sort_order, service_id';
        $result = $this->query($sql);
        $service_type = array();
        foreach ($result as $row) {
            $service_type[$row['service_id']]['service_name'] = $row['service_name'];
            $service_type[$row['service_id']]['service_id'] = $row['service_id'];
            $service_type[$row['service_id']]['service_desc'] = $row['service_desc'];
            $service_type[$row['service_id']]['received_days'] = $row['received_days'];
            $service_type[$row['service_id']]['unreceived_days'] = $row['unreceived_days'];
            $service_type[$row['service_id']]['url'] = url('user/returns_apply', array('id' => $row['service_id'], 'order_id' => $order_id, 'rec_id' => $rec_id));
        }
        return $service_type;
    }

    /**
     * 获取顶级退换货原因 by ECTouch Leah
     */
    public function get_parent_cause() {
        $sql = "SELECT * FROM " . $this->pre . "return_cause WHERE parent_id = 0  AND is_show = 1  ORDER BY sort_order";
        $result = $this->query($sql);
        if (is_array($result)) {
            $select = '';
            foreach ($result AS $var) {
                $select .= '<option value="' . $var['cause_id'] . '" ';
                $select .= ($selected == $var['cause_id']) ? "selected='ture'" : '';
                $select .= '>';
                if ($var['level'] > 0) {
                    $select .= str_repeat('&nbsp;', $var['level'] * 4);
                }
                $select .= htmlspecialchars(addslashes($var['cause_name']), ENT_QUOTES) . '</option>';
            }
            return $select;
        } else {
            return array();
        }
    }

    /**
     * 查询订单商品是否已申请过服务
     * @param type $rec_id
     * @return type
     */
    public function check_aftermarket($rec_id) {

        $service = $this->model->table('order_return')->field('COUNT(*) as count')->where('rec_id = ' . $rec_id)->find();
        return $service['count'];
    }

    /**
     * 当前可执行售后操作
     * @param $service_type
     * @return array
     */
    public function get_aftermarket_operate($service_type) {

        $operate = array();
        /**
         * 退货退款
         */
        if ($service_type == ST_RETURN_GOODS) {
            $operate['return_gods'] = true;
        } /* 仅退款* */ elseif ($service_type == ST_REFUND) {
            $operate['refund'] = true;
        } /* 退货退款* */ elseif ($service_type == ST_EXCHANGE) {

            $operate['exchange'] = true;
        } /* 维修* */ elseif ($service_type == ST_REPAIR) {
            $operate['repair'] = true;
        }
        return $operate;
    }

    /**退换货**/
    function tuihuanhuo($user_id) {
       $where['user_id'] = $user_id;
       $count = $this->model->table('order_return')->where($where)->count();
       return $count;
    }
    /**
     *  获取用户指定范围的订单列表
     *
     * @access  public
     * @param   int $user_id 用户ID号
     * @param   int $num 列表最大数量
     * @param   int $start 列表起始位置
     * @return  array       $order_list     订单列表
     */
    function get_user_aftermarket($user_id, $num = 10, $start = 0) {
        /* 取得订单列表 */
        $arr = array();

        $sql = "SELECT ret_id ,rec_id, goods_id, service_sn, order_sn, order_id,add_time, should_return, return_status, refund_status, is_check " .
                " FROM " . $this->pre .
                "order_return WHERE user_id = '$user_id'  ORDER BY add_time DESC LIMIT $start , $num";
        $res = M()->query($sql);
        foreach ($res as $key => $value) {
            if ($value['order_status'] == RF_APPLICATION) {
                $value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\" onclick=\"if (!confirm('" . L('confirm_cancel') . "')) return false;\">" . L('cancel') . "</a>";
            } else if ($value['is_check'] == RC_APPLY_SUCCESS) {
                /* 对配送状态的处理 */
                if ($value['shipping_status'] == SS_SHIPPED) {
                    @$value['handler'] = "<a href=\"" . url('user/affirm_received', array('order_id' => $value['order_id'])) . "\" onclick=\"if (!confirm('" . L('confirm_received') . "')) return false;\">" . L('received') . "</a>";
                } elseif ($value['shipping_status'] == SS_RECEIVED) {
                    @$value['handler'] = '<span style="color:red">' . L('ss_received') . '</span>';
                } else {
                    if ($value['pay_status'] == PS_UNPAYED) {
                        @$value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\">" . L('pay_money') . "</a>";
                    } else {
                        @$value['handler'] = "<a href=\"" . url('user/cancel_order', array('order_id' => $value['order_id'])) . "\">" . L('view_order') . "</a>";
                    }
                }
            } else {
                $value['handler'] = '<span>' . L('os.' . $value['order_status']) . '</span>';
            }

            $value['shipping_status'] = ($value['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $value['shipping_status'];
            $value['order_status'] = L('os.' . $value['order_status']) . ',' . L('ps.' . $value['pay_status']) . ',' . L('ss.' . $value['shipping_status']);

            $arr[] = array(
                'ret_id' => $value['ret_id'],
                'order_id' => $value['order_id'],
                'service_sn' => $value['service_sn'],
                'img' => get_image_path(0, model('Order')->get_order_thumb($value['order_id'])),
                'order_time' => local_date(C('time_format'), $value['add_time']),
                'return_status' => L('rf.' . $value['return_status']),
                'refund_status' => L('ff.' . $value['refund_status']),
                'total_fee' => price_format($value['total_fee'], false),
                'url' => url('user/order_detail', array('order_id' => $value['order_id'])),
                'goods_count' => model('Users')->get_order_goods_count($value['order_id']),
                'handler' => $value['handler']);
        }
        return $arr;
    }

    /**
     *  获取指服务订单的详情
     *
     * @access  public
     * @param   int $ret_id 订单ID
     * @param   int $user_id 用户ID
     *
     * @return   arr        $order          订单所有信息的数组
     */
    function get_aftermarket_detail($ret_id, $user_id = 0) {

        $ret_id = intval($ret_id);
        if ($ret_id <= 0) {
            ECTouch::err()->add(L('invalid_order_id'));

            return false;
        }
        $aftermarket = model('Order')->aftermarket_info($ret_id);

        //检查订单是否属于该用户
        if ($user_id > 0 && $user_id != $aftermarket['user_id']) {
            ECTouch::err()->add(L('no_priv'));

            return false;
        }
        return $aftermarket;
    }

    /**
     * 获取商家地址
     */
    function get_business_address($suppliers_id) {


        $address = '';
        if ($suppliers_id) {
            $address = '';
        } else {
            $sql = "SELECT region_name FROM " . $this->pre .
                "region WHERE region_id = '".C('shop_country')."'";
            $adress = $this->query($sql);
            //dump($adress);exit;
            $sql = "SELECT region_name FROM " . $this->pre .
                "region WHERE region_id = '".C('shop_province')."'";
            $adress = $this->query($sql);
            //dump($adress);exit;
            $sql = "SELECT region_name FROM " . $this->pre .
                "region WHERE region_id = '".C('shop_city')."'";
            $adress = $this->query($sql);

            $address.= C('shop_address') . '收件人：' . C('shop_name') . '联系电话：' . C('service_phone');
        }
        return $address;
    }

     /**
     * 获取省，市，地区id
     */
    function find_address($region_name,$region_type = 0) {

    	$sql = "SELECT region_id FROM " . $this->pre .
    		"region where region_name like '%$region_name%' and region_type = $region_type ";
    	$address = $this->row($sql);
        return $address['region_id'];

    }

    /**
     * 合并会员数据
     * @access  public
     * @param   id $from_user_id 原会员id
     * @param   id $to_user_id 新会员id
     * @return  boolen      $bool
     */
    function merge_user($from_user_id = 0, $to_user_id = 0)
    {
        if ($from_user_id > 0 && $to_user_id > 0 && $from_user_id != $to_user_id){
            // users表
            $from_user_info = $this->model->table('users')->field('*')->where(array('user_id'=> $from_user_id))->find();

            if(!empty($from_user_info)){
                // 更新字段值 email,sex,birthday,address_id,user_rank,is_special,parent_id,flag,alias,msn,qq,office_phone,home_phone,mobile_phone,is_validated
                // 组合数据
                $from_data = array(
                    'email' => $from_user_info['email'],
                    'sex' => $from_user_info['sex'],
                    'birthday' => $from_user_info['birthday'],
                    'address_id' => $from_user_info['address_id'],
                    'user_rank' => $from_user_info['user_rank'],
                    'is_special' => $from_user_info['is_special'],
                    'parent_id' => $from_user_info['parent_id'],
                    'flag' => $from_user_info['flag'],
                    'alias' => $from_user_info['alias'],
                    'msn' => $from_user_info['msn'],
                    'qq' => $from_user_info['qq'],
                    'office_phone' => $from_user_info['office_phone'],
                    'home_phone' => $from_user_info['home_phone'],
                    'mobile_phone' => $from_user_info['mobile_phone'],
                    'is_validated' => $from_user_info['is_validated'],
                    );
                $this->model->table('users')->data($from_data)->where(array('user_id' => $to_user_id))->update();

                // 累加字段值 user_money,frozen_money,pay_points,rank_points,credit_line
                $sql = "UPDATE {pre}users SET user_money = user_money + '" . $from_user_info['user_money'] . "', frozen_money = frozen_money + '" . $from_user_info['frozen_money'] . "', pay_points = pay_points + '" . $from_user_info['pay_points'] . "', rank_points = rank_points + '" . $from_user_info['rank_points'] . "', credit_line = credit_line + '" . $from_user_info['credit_line'] . "'  WHERE user_id = '$to_user_id'";

                $this->model->query($sql);
            }

            // 用户订单
            $from_order_info = $this->model->table('order_info')->field('order_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_order_info)){
                foreach ($from_order_info as $key => $value) {
                    $this->model->table('order_info')->data('user_id = ' . $to_user_id)->where('order_id = ' . $value['order_id'])->update();
                }
            }

            // 用户缺货登记
            $from_booking_goods = $this->model->table('booking_goods')->field('rec_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_booking_goods)){
                foreach ($from_booking_goods as $key => $value) {
                    $this->model->table('booking_goods')->data('user_id = ' . $to_user_id)->where('rec_id = ' . $value['rec_id'])->update();
                }
            }
            // 会员收藏商品
            $from_collect_goods = $this->model->table('collect_goods')->field('rec_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_collect_goods)){
                foreach ($from_collect_goods as $key => $value) {
                    $this->model->table('collect_goods')->data('user_id = ' . $to_user_id)->where('rec_id = ' . $value['rec_id'])->update();
                }
            }
            // 会员留言
            $from_feedback = $this->model->table('feedback')->field('msg_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_feedback)){
                $to_user_info = $this->model->table('users')->field('user_name')->where(array('user_id'=> $to_user_id))->find();
                foreach ($from_feedback as $key => $value) {
                    $setdata = array('user_id' => $to_user_id, 'user_name' => $to_user_info['user_name']);
                    $this->model->table('feedback')->data($setdata)->where('msg_id = ' . $value['msg_id'])->update();
                }
            }
            // 会员地址
            $from_user_address = $this->model->table('user_address')->field('address_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_user_address)){
                foreach ($from_user_address as $key => $value) {
                    $this->model->table('user_address')->data('user_id = ' . $to_user_id)->where('address_id = ' . $value['address_id'])->update();
                }
            }
            // 会员红包
            $from_user_bonus = $this->model->table('user_bonus')->field('bonus_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_user_bonus)){
                foreach ($from_user_bonus as $key => $value) {
                    $this->model->table('user_bonus')->data('user_id = ' . $to_user_id)->where('bonus_id = ' . $value['bonus_id'])->update();
                }
            }
            // 用户帐号金额
            $from_user_account = $this->model->table('user_account')->field('id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_user_account)){
                foreach ($from_user_account as $key => $value) {
                    $this->model->table('user_account')->data('user_id = ' . $to_user_id)->where('id = ' . $value['id'])->update();
                }
            }
            // 用户标记
            $from_tag = $this->model->table('tag')->field('tag_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_tag)){
                foreach ($from_tag as $key => $value) {
                    $this->model->table('tag')->data('user_id = ' . $to_user_id)->where('tag_id = ' . $value['tag_id'])->update();
                }
            }
            // 用户日志
            $from_account_log = $this->model->table('account_log')->field('log_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_account_log)){
                foreach ($from_account_log as $key => $value) {
                    $this->model->table('account_log')->data('user_id = ' . $to_user_id)->where('log_id = ' . $value['log_id'])->update();
                }
            }

            // 用户评论
            $from_comment = $this->model->table('comment')->field('comment_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_comment) && !empty($to_user_info)){
                foreach ($from_comment as $key => $value) {
                    $setdata = array('user_id' => $to_user_id, 'user_name' => $to_user_info['user_name'], 'email' => $to_user_info['email']);
                    $this->model->table('comment')->data($setdata)->where('comment_id = ' . $value['comment_id'])->update();
                }
            }

            // 用户退换货
            $from_order_return = $this->model->table('order_return')->field('ret_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_order_return)){
                foreach ($from_order_return as $key => $value) {
                    $this->model->table('order_return')->data('user_id = ' . $to_user_id)->where('ret_id = ' . $value['ret_id'])->update();
                }
            }

            /*DRP_START*/
            // 分销商
            $from_drp_shop = $this->model->table('drp_shop')->field('id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_drp_shop)){
                foreach ($from_drp_shop as $key => $value) {
                    $this->model->table('drp_shop')->data('user_id = ' . $to_user_id)->where('id = ' . $value['id'])->update();
                }
            }
            // 分销访客
            $from_drp_drp_visiter = $this->model->table('drp_visiter')->field('id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_drp_visiter)){
                foreach ($from_drp_visiter as $key => $value) {
                    $this->model->table('drp_visiter')->data('user_id = ' . $to_user_id)->where('id = ' . $value['id'])->update();
                }
            }
            // 分销资金明细
            $from_drp_log = $this->model->table('drp_log')->field('log_id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_drp_log)){
                foreach ($from_drp_log as $key => $value) {
                    $this->model->table('drp_log')->data('user_id = ' . $to_user_id)->where('log_id = ' . $value['id'])->update();
                }
            }
            // 分销用户提现银行卡
            $from_drp_bank = $this->model->table('drp_bank')->field('id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_drp_bank)){
                foreach ($from_drp_bank as $key => $value) {
                    $this->model->table('drp_bank')->data('user_id = ' . $to_user_id)->where('id = ' . $value['id'])->update();
                }
            }
            // 购买微分销记录
            $from_drp_apply = $this->model->table('drp_apply')->field('id')->where(array('user_id'=> $from_user_id))->select();
            if(!empty($from_drp_apply)){
                foreach ($from_drp_apply as $key => $value) {
                    $this->model->table('drp_apply')->data('user_id = ' . $to_user_id)->where('id = ' . $value['id'])->update();
                }
            }
            /*DRP_END*/
            return true;
        }else{
            return false;
        }
    }

    /**
     * 查询会员信息
     * @param  integer $user_id
     * @param  integer $wechat_id
     * @return array
     */
    function get_users($user_id)
    {
        $result = $this->model->table('users')->field('user_name, user_id, mobile_phone, email')->where(array('user_id' => $user_id))->find();
        return $result;
    }


    /**
     * 微信粉丝生成用户名规则
     * 长度最大15个字符 兼容UCenter用户名
     * @return
     */
    function get_wechat_username($unionid, $type = '')
    {
        switch ($type) {
            case 'weixin':
                $prefix = 'wx';
                break;
            case 'qq':
                $prefix = 'qq';
                break;
            case 'weibo':
                $prefix = 'wb';
                break;
            case 'facebook':
                $prefix = 'fb';
                break;
            default:
                $prefix = 'sc';
                break;
        }
        return $prefix . substr(md5($unionid), -5) . substr(time(), 0, 4) . mt_rand(1000, 9999);
    }

    /**
     * 非退货退款订单
     * @access  public
     * @param   id $user_id 原会员id
     * @return  string      $v 非退货退款订单字符串
     */
    public function order_rec_id($user_id){
        $sql = "SELECT rec_id FROM ". $this->pre ."order_return where user_id = '$user_id' and service_id = 1";
        $result = $this->query($sql);

        foreach($result as $key =>$val){
            if($val['rec_id']){
                $t = $val['rec_id'];
                $v .= $t.",";
            }
        }
        $v = substr($v,0,-1) ;
        return $v;
    }
    
    /**
     * 查询用户openid
     * @access  public
     * @param  $user_id 原会员id
     * @return $openid  用户openid
     */
    function get_openid($user_id) {
        $result = $this->model->table('wechat_user')->field('openid')->where(array('ect_uid' => $user_id))->find();
        return $result['openid'];
    }

    /**
     * 更新微信用户信息
     * @param array    $info 微信用户信息
     * @param string   $wechat_id  公众号ID
     * @return
     */
    public function add_wechat_user($info, $wechat_id = '')
    {
        //公众号id
        $wechat = $this->model->table('wechat')->field('id')->where(array('type' => 2, 'status' => 1, 'default_wx' => 1))->find();
        $wechat_id = !empty($wechat_id) ? $wechat_id : $wechat['id'];
        // 组合数据
        $data = array(
            'wechat_id' => $wechat_id,
            'openid' => $info['openid'],
            'nickname' => !empty($info['nickname']) ? $info['nickname'] : '',
            'sex' => !empty($info['sex']) ? $info['sex'] : 0,
            'language' => !empty($info['language']) ? $info['language'] : '',
            'city' => !empty($info['city']) ? $info['city'] : '',
            'province' => !empty($info['province']) ? $info['province'] : '',
            'country' => !empty($info['country']) ? $info['country'] : '',
            'headimgurl' => !empty($info['headimgurl']) ? $info['headimgurl'] : '',
            'unionid' => $info['unionid'],
            'ect_uid' => 0,
        );

        // unionid 微信开放平台唯一标识
        if (!empty($info['unionid'])) {
            // 查询
            $where = array('unionid' => $info['unionid'], 'wechat_id' => $wechat_id);
            $result = $this->model->table('wechat_user')->field('openid, unionid')->where($where)->find();
            if (empty($result)) {
                // 新增记录
                $this->model->table('wechat_user')->data($data)->insert();
            } else {
                // 更新记录
                $this->model->table('wechat_user')->data($data)->where($where)->update();
            }
        }
    }

    /**
    *同步sina/qq老会员
    **/
    public function get_old_outh_user_info($data, $type){
        $aite_id = $type.'_'.$data['unionid'];
        $where = array('aite_id' => $aite_id);
        //用户是否用sina/qq产生过会员
        $res = $this->model->table('touch_user_info')->field('user_id')->where($where)->find();
        if($res){
            $where = array('user_id' => $res['user_id']);
            $ress = $this->model->table('connect_user')->field('id')->where($where)->find();
            if(!$ress){
                $data['user_id'] = $res['user_id'];
                $type = $type == 'qq' ? $type : 'weibo';
                model('Users')->update_connnect_user($data, $type); 
            }
        }
    }

}