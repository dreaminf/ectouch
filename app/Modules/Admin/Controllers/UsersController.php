<?php

namespace App\Modules\Admin\Controllers;

/**
 * 会员管理程序
 * Class UsersController
 * @package App\Modules\Admin\Controllers
 */
class UsersController extends Controller
{
    public function actionIndex()
    {
        /**
         * 用户帐号列表
         */
        if ($_REQUEST['act'] == 'list') {
            /* 检查权限 */
            admin_priv('users_manage');
            $sql = "SELECT rank_id, rank_name, min_points FROM " . $this->ecs->table('user_rank') . " ORDER BY min_points ASC ";
            $rs = $this->db->query($sql);

            $ranks = [];
            foreach ($rs as $row) {
                $ranks[$row['rank_id']] = $row['rank_name'];
            }

            $this->smarty->assign('user_ranks', $ranks);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['03_users_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['04_users_add'], 'href' => 'users.php?act=add']);

            $user_list = $this->user_list();

            $this->smarty->assign('user_list', $user_list['user_list']);
            $this->smarty->assign('filter', $user_list['filter']);
            $this->smarty->assign('record_count', $user_list['record_count']);
            $this->smarty->assign('page_count', $user_list['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('sort_user_id', '<img src="' . __TPL__ . '/images/sort_desc.gif">');

            return $this->smarty->display('users_list.htm');
        }

        /**
         * ajax返回用户列表
         */
        if ($_REQUEST['act'] == 'query') {
            $user_list = $this->user_list();

            $this->smarty->assign('user_list', $user_list['user_list']);
            $this->smarty->assign('filter', $user_list['filter']);
            $this->smarty->assign('record_count', $user_list['record_count']);
            $this->smarty->assign('page_count', $user_list['page_count']);

            $sort_flag = sort_flag($user_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('users_list.htm'), '', ['filter' => $user_list['filter'], 'page_count' => $user_list['page_count']]);
        }

        /**
         * 添加会员帐号
         */
        if ($_REQUEST['act'] == 'add') {
            /* 检查权限 */
            admin_priv('users_manage');

            $user = ['rank_points' => $GLOBALS['_CFG']['register_points'],
                'pay_points' => $GLOBALS['_CFG']['register_points'],
                'sex' => 0,
                'credit_line' => 0
            ];
            /* 取出注册扩展字段 */
            $sql = 'SELECT * FROM ' . $this->ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 AND id != 6 ORDER BY dis_order, id';
            $extend_info_list = $this->db->getAll($sql);
            $this->smarty->assign('extend_info_list', $extend_info_list);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['04_users_add']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['03_users_list'], 'href' => 'users.php?act=list']);
            $this->smarty->assign('form_action', 'insert');
            $this->smarty->assign('user', $user);
            $this->smarty->assign('special_ranks', get_rank_list(true));

            return $this->smarty->display('user_info.htm');
        }

        /**
         * 添加会员帐号
         */
        if ($_REQUEST['act'] == 'insert') {
            /* 检查权限 */
            admin_priv('users_manage');
            $username = empty($_POST['username']) ? '' : trim($_POST['username']);
            $password = empty($_POST['password']) ? '' : trim($_POST['password']);
            $email = empty($_POST['email']) ? '' : trim($_POST['email']);
            $sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
            $sex = in_array($sex, [0, 1, 2]) ? $sex : 0;
            $birthday = $_POST['birthdayYear'] . '-' . $_POST['birthdayMonth'] . '-' . $_POST['birthdayDay'];
            $rank = empty($_POST['user_rank']) ? 0 : intval($_POST['user_rank']);
            $credit_line = empty($_POST['credit_line']) ? 0 : floatval($_POST['credit_line']);

            $users =& init_users();

            if (!$users->add_user($username, $password, $email)) {
                /* 插入会员数据失败 */
                if ($users->error == ERR_INVALID_USERNAME) {
                    $msg = $GLOBALS['_LANG']['username_invalid'];
                } elseif ($users->error == ERR_USERNAME_NOT_ALLOW) {
                    $msg = $GLOBALS['_LANG']['username_not_allow'];
                } elseif ($users->error == ERR_USERNAME_EXISTS) {
                    $msg = $GLOBALS['_LANG']['username_exists'];
                } elseif ($users->error == ERR_INVALID_EMAIL) {
                    $msg = $GLOBALS['_LANG']['email_invalid'];
                } elseif ($users->error == ERR_EMAIL_NOT_ALLOW) {
                    $msg = $GLOBALS['_LANG']['email_not_allow'];
                } elseif ($users->error == ERR_EMAIL_EXISTS) {
                    $msg = $GLOBALS['_LANG']['email_exists'];
                } else {
                    //die('Error:'.$users->error_msg());
                }
                return sys_msg($msg, 1);
            }

            /* 注册送积分 */
            if (!empty($GLOBALS['_CFG']['register_points'])) {
                log_account_change(session('user_id'), 0, 0, $GLOBALS['_CFG']['register_points'], $GLOBALS['_CFG']['register_points'], $GLOBALS['_LANG']['register_points']);
            }

            /*把新注册用户的扩展信息插入数据库*/
            $sql = 'SELECT id FROM ' . $this->ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有扩展字段的id
            $fields_arr = $this->db->getAll($sql);

            $extend_field_str = '';    //生成扩展字段的内容字符串
            $user_id_arr = $users->get_profile_by_name($username);
            foreach ($fields_arr as $val) {
                $extend_field_index = 'extend_field' . $val['id'];
                if (!empty($_POST[$extend_field_index])) {
                    $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
                    $extend_field_str .= " ('" . $user_id_arr['user_id'] . "', '" . $val['id'] . "', '" . $temp_field_content . "'),";
                }
            }
            $extend_field_str = substr($extend_field_str, 0, -1);

            if ($extend_field_str) {      //插入注册扩展数据
                $sql = 'INSERT INTO ' . $this->ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
                $this->db->query($sql);
            }

            /* 更新会员的其它信息 */
            $other = [];
            $other['credit_line'] = $credit_line;
            $other['user_rank'] = $rank;
            $other['sex'] = $sex;
            $other['birthday'] = $birthday;
            $other['reg_time'] = local_strtotime(local_date('Y-m-d H:i:s'));

            $other['msn'] = isset($_POST['extend_field1']) ? htmlspecialchars(trim($_POST['extend_field1'])) : '';
            $other['qq'] = isset($_POST['extend_field2']) ? htmlspecialchars(trim($_POST['extend_field2'])) : '';
            $other['office_phone'] = isset($_POST['extend_field3']) ? htmlspecialchars(trim($_POST['extend_field3'])) : '';
            $other['home_phone'] = isset($_POST['extend_field4']) ? htmlspecialchars(trim($_POST['extend_field4'])) : '';
            $other['mobile_phone'] = isset($_POST['extend_field5']) ? htmlspecialchars(trim($_POST['extend_field5'])) : '';

            $this->db->autoExecute($this->ecs->table('users'), $other, 'UPDATE', "user_name = '$username'");

            /* 记录管理员操作 */
            admin_log($_POST['username'], 'add', 'users');

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list'];
            return sys_msg(sprintf($GLOBALS['_LANG']['add_success'], htmlspecialchars(stripslashes($_POST['username']))), 0, $link);
        }

        /**
         * 编辑用户帐号
         */
        if ($_REQUEST['act'] == 'edit') {
            /* 检查权限 */
            admin_priv('users_manage');

            $sql = "SELECT u.user_name, u.sex, u.birthday, u.pay_points, u.rank_points, u.user_rank , u.user_money, u.frozen_money, u.credit_line, u.parent_id, u2.user_name as parent_username, u.qq, u.msn, u.office_phone, u.home_phone, u.mobile_phone" .
                " FROM " . $this->ecs->table('users') . " u LEFT JOIN " . $this->ecs->table('users') . " u2 ON u.parent_id = u2.user_id WHERE u.user_id='$_GET[id]'";

            $row = $this->db->getRow($sql);
            $row['user_name'] = addslashes($row['user_name']);
            $users =& init_users();
            $user = $users->get_user_info($row['user_name']);

            $sql = "SELECT u.user_id, u.sex, u.birthday, u.pay_points, u.rank_points, u.user_rank , u.user_money, u.frozen_money, u.credit_line, u.parent_id, u2.user_name as parent_username, u.qq, u.msn,
    u.office_phone, u.home_phone, u.mobile_phone" .
                " FROM " . $this->ecs->table('users') . " u LEFT JOIN " . $this->ecs->table('users') . " u2 ON u.parent_id = u2.user_id WHERE u.user_id='$_GET[id]'";

            $row = $this->db->getRow($sql);

            if ($row) {
                $user['user_id'] = $row['user_id'];
                $user['sex'] = $row['sex'];
                $user['birthday'] = date($row['birthday']);
                $user['pay_points'] = $row['pay_points'];
                $user['rank_points'] = $row['rank_points'];
                $user['user_rank'] = $row['user_rank'];
                $user['user_money'] = $row['user_money'];
                $user['frozen_money'] = $row['frozen_money'];
                $user['credit_line'] = $row['credit_line'];
                $user['formated_user_money'] = price_format($row['user_money']);
                $user['formated_frozen_money'] = price_format($row['frozen_money']);
                $user['parent_id'] = $row['parent_id'];
                $user['parent_username'] = $row['parent_username'];
                $user['qq'] = $row['qq'];
                $user['msn'] = $row['msn'];
                $user['office_phone'] = $row['office_phone'];
                $user['home_phone'] = $row['home_phone'];
                $user['mobile_phone'] = $row['mobile_phone'];
            } else {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['username_invalid'], 0, $links);
//        $user['sex']            = 0;
//        $user['pay_points']     = 0;
//        $user['rank_points']    = 0;
//        $user['user_money']     = 0;
//        $user['frozen_money']   = 0;
//        $user['credit_line']    = 0;
//        $user['formated_user_money'] = price_format(0);
//        $user['formated_frozen_money'] = price_format(0);
            }

            /* 取出注册扩展字段 */
            $sql = 'SELECT * FROM ' . $this->ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 AND id != 6 ORDER BY dis_order, id';
            $extend_info_list = $this->db->getAll($sql);

            $sql = 'SELECT reg_field_id, content ' .
                'FROM ' . $this->ecs->table('reg_extend_info') .
                " WHERE user_id = $user[user_id]";
            $extend_info_arr = $this->db->getAll($sql);

            $temp_arr = [];
            foreach ($extend_info_arr as $val) {
                $temp_arr[$val['reg_field_id']] = $val['content'];
            }

            foreach ($extend_info_list as $key => $val) {
                switch ($val['id']) {
                    case 1:
                        $extend_info_list[$key]['content'] = $user['msn'];
                        break;
                    case 2:
                        $extend_info_list[$key]['content'] = $user['qq'];
                        break;
                    case 3:
                        $extend_info_list[$key]['content'] = $user['office_phone'];
                        break;
                    case 4:
                        $extend_info_list[$key]['content'] = $user['home_phone'];
                        break;
                    case 5:
                        $extend_info_list[$key]['content'] = $user['mobile_phone'];
                        break;
                    default:
                        $extend_info_list[$key]['content'] = empty($temp_arr[$val['id']]) ? '' : $temp_arr[$val['id']];
                }
            }

            $this->smarty->assign('extend_info_list', $extend_info_list);

            /* 当前会员推荐信息 */
            $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
            $this->smarty->assign('affiliate', $affiliate);

            empty($affiliate) && $affiliate = [];

            if (empty($affiliate['config']['separate_by'])) {
                //推荐注册分成
                $affdb = [];
                $num = count($affiliate['item']);
                $up_uid = "'$_GET[id]'";
                for ($i = 1; $i <= $num; $i++) {
                    $count = 0;
                    if ($up_uid) {
                        $sql = "SELECT user_id FROM " . $this->ecs->table('users') . " WHERE parent_id IN($up_uid)";
                        $query = $this->db->query($sql);
                        $up_uid = '';
                        foreach ($query as $rt) {
                            $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                            $count++;
                        }
                    }
                    $affdb[$i]['num'] = $count;
                }
                if ($affdb[1]['num'] > 0) {
                    $this->smarty->assign('affdb', $affdb);
                }
            }

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['users_edit']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['03_users_list'], 'href' => 'users.php?act=list&' . list_link_postfix()]);
            $this->smarty->assign('user', $user);
            $this->smarty->assign('form_action', 'update');
            $this->smarty->assign('special_ranks', get_rank_list(true));

            return $this->smarty->display('user_info.htm');
        }

        /**
         * 更新用户帐号
         */
        if ($_REQUEST['act'] == 'update') {
            /* 检查权限 */
            admin_priv('users_manage');
            $username = empty($_POST['username']) ? '' : trim($_POST['username']);
            $password = empty($_POST['password']) ? '' : trim($_POST['password']);
            $email = empty($_POST['email']) ? '' : trim($_POST['email']);
            $sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
            $sex = in_array($sex, [0, 1, 2]) ? $sex : 0;
            $birthday = $_POST['birthdayYear'] . '-' . $_POST['birthdayMonth'] . '-' . $_POST['birthdayDay'];
            $rank = empty($_POST['user_rank']) ? 0 : intval($_POST['user_rank']);
            $credit_line = empty($_POST['credit_line']) ? 0 : floatval($_POST['credit_line']);

            $users =& init_users();

            if (!$users->edit_user(['username' => $username, 'password' => $password, 'email' => $email, 'gender' => $sex, 'bday' => $birthday], 1)) {
                if ($users->error == ERR_EMAIL_EXISTS) {
                    $msg = $GLOBALS['_LANG']['email_exists'];
                } else {
                    $msg = $GLOBALS['_LANG']['edit_user_failed'];
                }
                return sys_msg($msg, 1);
            }
            if (!empty($password)) {
                $sql = "UPDATE " . $this->ecs->table('users') . "SET `ec_salt`='0' WHERE user_name= '" . $username . "'";
                $this->db->query($sql);
            }
            /* 更新用户扩展字段的数据 */
            $sql = 'SELECT id FROM ' . $this->ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有扩展字段的id
            $fields_arr = $this->db->getAll($sql);
            $user_id_arr = $users->get_profile_by_name($username);
            $user_id = $user_id_arr['user_id'];

            foreach ($fields_arr as $val) {       //循环更新扩展用户信息
                $extend_field_index = 'extend_field' . $val['id'];
                if (isset($_POST[$extend_field_index])) {
                    $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];

                    $sql = 'SELECT * FROM ' . $this->ecs->table('reg_extend_info') . "  WHERE reg_field_id = '$val[id]' AND user_id = '$user_id'";
                    if ($this->db->getOne($sql)) {      //如果之前没有记录，则插入
                        $sql = 'UPDATE ' . $this->ecs->table('reg_extend_info') . " SET content = '$temp_field_content' WHERE reg_field_id = '$val[id]' AND user_id = '$user_id'";
                    } else {
                        $sql = 'INSERT INTO ' . $this->ecs->table('reg_extend_info') . " (`user_id`, `reg_field_id`, `content`) VALUES ('$user_id', '$val[id]', '$temp_field_content')";
                    }
                    $this->db->query($sql);
                }
            }


            /* 更新会员的其它信息 */
            $other = [];
            $other['credit_line'] = $credit_line;
            $other['user_rank'] = $rank;

            $other['msn'] = isset($_POST['extend_field1']) ? htmlspecialchars(trim($_POST['extend_field1'])) : '';
            $other['qq'] = isset($_POST['extend_field2']) ? htmlspecialchars(trim($_POST['extend_field2'])) : '';
            $other['office_phone'] = isset($_POST['extend_field3']) ? htmlspecialchars(trim($_POST['extend_field3'])) : '';
            $other['home_phone'] = isset($_POST['extend_field4']) ? htmlspecialchars(trim($_POST['extend_field4'])) : '';
            $other['mobile_phone'] = isset($_POST['extend_field5']) ? htmlspecialchars(trim($_POST['extend_field5'])) : '';

            $this->db->autoExecute($this->ecs->table('users'), $other, 'UPDATE', "user_name = '$username'");

            /* 记录管理员操作 */
            admin_log($username, 'edit', 'users');

            /* 提示信息 */
            $links[0]['text'] = $GLOBALS['_LANG']['goto_list'];
            $links[0]['href'] = 'users.php?act=list&' . list_link_postfix();
            $links[1]['text'] = $GLOBALS['_LANG']['go_back'];
            $links[1]['href'] = 'javascript:history.back()';

            return sys_msg($GLOBALS['_LANG']['update_success'], 0, $links);
        }

        /**
         * 批量删除会员帐号
         */
        if ($_REQUEST['act'] == 'batch_remove') {
            /* 检查权限 */
            admin_priv('users_drop');

            if (isset($_POST['checkboxes'])) {
                $sql = "SELECT user_name FROM " . $this->ecs->table('users') . " WHERE user_id " . db_create_in($_POST['checkboxes']);
                $col = $this->db->getCol($sql);
                $usernames = implode(',', addslashes_deep($col));
                $count = count($col);
                /* 通过插件来删除用户 */
                $users =& init_users();
                $users->remove_user($col);

                admin_log($usernames, 'batch_remove', 'users');

                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list'];
                return sys_msg(sprintf($GLOBALS['_LANG']['batch_remove_success'], $count), 0, $lnk);
            } else {
                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['no_select_user'], 0, $lnk);
            }
        }

        /* 编辑用户名 */
        if ($_REQUEST['act'] == 'edit_username') {
            /* 检查权限 */
            check_authz_json('users_manage');

            $username = empty($_REQUEST['val']) ? '' : json_str_iconv(trim($_REQUEST['val']));
            $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

            if ($id == 0) {
                return make_json_error('NO USER ID');
                return;
            }

            if ($username == '') {
                return make_json_error($GLOBALS['_LANG']['username_empty']);
                return;
            }

            $users =& init_users();

            if ($users->edit_user($id, $username)) {
                if ($GLOBALS['_CFG']['integrate_code'] != 'ecshop') {
                    /* 更新商城会员表 */
                    $this->db->query('UPDATE ' . $this->ecs->table('users') . " SET user_name = '$username' WHERE user_id = '$id'");
                }

                admin_log(addslashes($username), 'edit', 'users');
                return make_json_result(stripcslashes($username));
            } else {
                $msg = ($users->error == ERR_USERNAME_EXISTS) ? $GLOBALS['_LANG']['username_exists'] : $GLOBALS['_LANG']['edit_user_failed'];
                return make_json_error($msg);
            }
        }

        /**
         * 编辑email
         */
        if ($_REQUEST['act'] == 'edit_email') {
            /* 检查权限 */
            check_authz_json('users_manage');

            $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
            $email = empty($_REQUEST['val']) ? '' : json_str_iconv(trim($_REQUEST['val']));

            $users =& init_users();

            $sql = "SELECT user_name FROM " . $this->ecs->table('users') . " WHERE user_id = '$id'";
            $username = $this->db->getOne($sql);


            if (is_email($email)) {
                if ($users->edit_user(['username' => $username, 'email' => $email])) {
                    admin_log(addslashes($username), 'edit', 'users');

                    return make_json_result(stripcslashes($email));
                } else {
                    $msg = ($users->error == ERR_EMAIL_EXISTS) ? $GLOBALS['_LANG']['email_exists'] : $GLOBALS['_LANG']['edit_user_failed'];
                    return make_json_error($msg);
                }
            } else {
                return make_json_error($GLOBALS['_LANG']['invalid_email']);
            }
        }

        /**
         * 删除会员帐号
         */
        if ($_REQUEST['act'] == 'remove') {
            /* 检查权限 */
            admin_priv('users_drop');

            $sql = "SELECT user_name FROM " . $this->ecs->table('users') . " WHERE user_id = '" . $_GET['id'] . "'";
            $username = $this->db->getOne($sql);
            /* 通过插件来删除用户 */
            $users =& init_users();
            $users->remove_user($username); //已经删除用户所有数据

            /* 记录管理员操作 */
            admin_log(addslashes($username), 'remove', 'users');

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list'];
            return sys_msg(sprintf($GLOBALS['_LANG']['remove_success'], $username), 0, $link);
        }

        /**
         * 收货地址查看
         */
        if ($_REQUEST['act'] == 'address_list') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $sql = "SELECT a.*, c.region_name AS country_name, p.region_name AS province, ct.region_name AS city_name, d.region_name AS district_name " .
                " FROM " . $this->ecs->table('user_address') . " as a " .
                " LEFT JOIN " . $this->ecs->table('region') . " AS c ON c.region_id = a.country " .
                " LEFT JOIN " . $this->ecs->table('region') . " AS p ON p.region_id = a.province " .
                " LEFT JOIN " . $this->ecs->table('region') . " AS ct ON ct.region_id = a.city " .
                " LEFT JOIN " . $this->ecs->table('region') . " AS d ON d.region_id = a.district " .
                " WHERE user_id='$id'";
            $address = $this->db->getAll($sql);
            $this->smarty->assign('address', $address);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['address_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['03_users_list'], 'href' => 'users.php?act=list&' . list_link_postfix()]);
            return $this->smarty->display('user_address_list.htm');
        }

        /**
         * 脱离推荐关系
         */
        if ($_REQUEST['act'] == 'remove_parent') {
            /* 检查权限 */
            admin_priv('users_manage');

            $sql = "UPDATE " . $this->ecs->table('users') . " SET parent_id = 0 WHERE user_id = '" . $_GET['id'] . "'";
            $this->db->query($sql);

            /* 记录管理员操作 */
            $sql = "SELECT user_name FROM " . $this->ecs->table('users') . " WHERE user_id = '" . $_GET['id'] . "'";
            $username = $this->db->getOne($sql);
            admin_log(addslashes($username), 'edit', 'users');

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list'];
            return sys_msg(sprintf($GLOBALS['_LANG']['update_success'], $username), 0, $link);
        }

        /**
         * 查看用户推荐会员列表
         */
        if ($_REQUEST['act'] == 'aff_list') {
            /* 检查权限 */
            admin_priv('users_manage');
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['03_users_list']);

            $auid = $_GET['auid'];
            $user_list['user_list'] = [];

            $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
            $this->smarty->assign('affiliate', $affiliate);

            empty($affiliate) && $affiliate = [];

            $num = count($affiliate['item']);
            $up_uid = "'$auid'";
            $all_count = 0;
            for ($i = 1; $i <= $num; $i++) {
                $count = 0;
                if ($up_uid) {
                    $sql = "SELECT user_id FROM " . $this->ecs->table('users') . " WHERE parent_id IN($up_uid)";
                    $query = $this->db->query($sql);
                    $up_uid = '';
                    foreach ($query as $rt) {
                        $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                        $count++;
                    }
                }
                $all_count += $count;

                if ($count) {
                    $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time " .
                        " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                        " ORDER by level, user_id";
                    $user_list['user_list'] = array_merge($user_list['user_list'], $this->db->getAll($sql));
                }
            }

            $temp_count = count($user_list['user_list']);
            for ($i = 0; $i < $temp_count; $i++) {
                $user_list['user_list'][$i]['reg_time'] = local_date($GLOBALS['_CFG']['date_format'], $user_list['user_list'][$i]['reg_time']);
            }

            $user_list['record_count'] = $all_count;

            $this->smarty->assign('user_list', $user_list['user_list']);
            $this->smarty->assign('record_count', $user_list['record_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['back_note'], 'href' => "users.php?act=edit&id=$auid"]);

            return $this->smarty->display('affiliate_list.htm');
        }
    }

    /**
     *  返回用户列表数据
     *
     * @access  public
     * @param
     *
     * @return void
     */
    private function user_list()
    {
        $result = get_filter();
        if ($result === false) {
            /* 过滤条件 */
            $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
            if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
                $filter['keywords'] = json_str_iconv($filter['keywords']);
            }
            $filter['rank'] = empty($_REQUEST['rank']) ? 0 : intval($_REQUEST['rank']);
            $filter['pay_points_gt'] = empty($_REQUEST['pay_points_gt']) ? 0 : intval($_REQUEST['pay_points_gt']);
            $filter['pay_points_lt'] = empty($_REQUEST['pay_points_lt']) ? 0 : intval($_REQUEST['pay_points_lt']);

            $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'user_id' : trim($_REQUEST['sort_by']);
            $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

            $ex_where = ' WHERE 1 ';
            if ($filter['keywords']) {
                $ex_where .= " AND user_name LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
            }
            if ($filter['rank']) {
                $sql = "SELECT min_points, max_points, special_rank FROM " . $GLOBALS['ecs']->table('user_rank') . " WHERE rank_id = '$filter[rank]'";
                $row = $GLOBALS['db']->getRow($sql);
                if ($row['special_rank'] > 0) {
                    /* 特殊等级 */
                    $ex_where .= " AND user_rank = '$filter[rank]' ";
                } else {
                    $ex_where .= " AND rank_points >= " . intval($row['min_points']) . " AND rank_points < " . intval($row['max_points']);
                }
            }
            if ($filter['pay_points_gt']) {
                $ex_where .= " AND pay_points >= '$filter[pay_points_gt]' ";
            }
            if ($filter['pay_points_lt']) {
                $ex_where .= " AND pay_points < '$filter[pay_points_lt]' ";
            }

            $filter['record_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('users') . $ex_where);

            /* 分页大小 */
            $filter = page_and_size($filter);
            $sql = "SELECT user_id, user_name, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time " .
                " FROM " . $GLOBALS['ecs']->table('users') . $ex_where .
                " ORDER by " . $filter['sort_by'] . ' ' . $filter['sort_order'] .
                " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

            $filter['keywords'] = stripslashes($filter['keywords']);
            set_filter($filter, $sql);
        } else {
            $sql = $result['sql'];
            $filter = $result['filter'];
        }

        $user_list = $GLOBALS['db']->getAll($sql);

        $count = count($user_list);
        for ($i = 0; $i < $count; $i++) {
            $user_list[$i]['reg_time'] = local_date($GLOBALS['_CFG']['date_format'], $user_list[$i]['reg_time']);
        }

        $arr = ['user_list' => $user_list, 'filter' => $filter,
            'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}