<?php

/**
 * ECSHOP 程序说明
 * ===========================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ==========================================================
 * $Author: liubo $
 * $Id: affiliate.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECTOUCH', true);
require(dirname(__FILE__) . '/includes/init.php');
admin_priv('affiliate');
$config = get_affiliate();

/*------------------------------------------------------ */
//-- 分成管理页
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 获取分类列表 */
    $cate_list = $db->getAll("SELECT c.cat_name, c.cat_id, p.profit_id, p.cate_id, p.profit1, p.profit2, p.profit3 FROM " . $ecs->table("category") . "as c left join " . $ecs->table('drp_profit') . " as p on c.cat_id=p.cate_id WHERE parent_id= 0 and is_show = 1");

    foreach($cate_list as $key=>$val){
        $cate_list[$key]['profit1'] = $val['profit1'] > 0 ? $val['profit1'] : 0;
        $cate_list[$key]['profit2'] = $val['profit2'] > 0 ? $val['profit2'] : 0;
        $cate_list[$key]['profit3'] = $val['profit3'] > 0 ? $val['profit3'] : 0;
    }
    $smarty->assign('list', $cate_list);
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $smarty->assign('ur_here', $_LANG['drp_profit']);
    $smarty->display('drp_cate_list.htm');
}

/*------------------------------------------------------ */
//-- 设置分销利润
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    if($_POST){
        $cate_id = $_POST['cate_id'] ? $_POST['cate_id'] : 0;
        if($cate_id == 0){
            ecs_header("Location: drp.php?act=list\n");
            exit;
        }
        $data = $_POST['data'];
        foreach($data as $key=>$val){
            if(!is_numeric($val) || $val < 0 || $val > 100){
                $data[$key] = 0;
            }
        }
        if($db->getRow("SELECT profit_id FROM" . $ecs->table('drp_profit') . " WHERE  cate_id=$cate_id")){
            $db->autoExecute($ecs->table('drp_profit'), $data, 'UPDATE', "cate_id = '$cate_id'");
        }else{
            $data['cate_id'] = $cate_id;
            $db->autoExecute($ecs->table('drp_profit'), $data, 'INSERT');
        }
        ecs_header("Location: drp.php?act=list\n");
        exit;
    }
    $id = $_GET['id'] ? $_GET['id'] : 0;
    if($id == 0){
        ecs_header("Location: drp.php?act=list\n");
        exit;
    }
    /* 获取分类列表 */
    $cate_list = $db->getRow("SELECT c.cat_name, c.cat_id, p.profit_id, p.cate_id, p.profit1, p.profit2, p.profit3 FROM " . $ecs->table("category") . "as c left join " . $ecs->table('drp_profit') . " as p on c.cat_id=p.cate_id WHERE parent_id= 0 and is_show = 1 and cat_id=$id");


    $cate_list['profit1'] = $cate_list['profit1'] > 0 ? $cate_list['profit1'] : 0;
    $cate_list['profit2'] = $cate_list['profit2'] > 0 ? $cate_list['profit2'] : 0;
    $cate_list['profit3'] = $cate_list['profit3'] > 0 ? $cate_list['profit3'] : 0;

    $smarty->assign('list', $cate_list);
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $smarty->assign('ur_here', $_LANG['drp_profit']);
    $smarty->display('drp_cate_edit.htm');
}


/*------------------------------------------------------ */
//-- 分销设置
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'config')
{
    if($_POST){
        $data = $_POST['data'];
        if($data){
            foreach($data as $key=>$val){
                unset($dat);
                $dat['centent']=$val;
                $db->autoExecute($ecs->table('drp_config'), $dat, 'UPDATE', "keyword = '$key'");
            }
        }

        ecs_header("Location: drp.php?act=config\n");
        exit;
    }
    $info = $db->getAll("SELECT * FROM " . $ecs->table("drp_config"));
    $smarty->assign('info', $info);
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $smarty->assign('ur_here', $_LANG['config']);
    $smarty->display('drp_config.htm');
}

/*------------------------------------------------------ */
//-- 分销商管理
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'users')
{
    $list = $db->getAll("SELECT d.id,d.shop_name,d.real_name,d.shop_mobile,d.user_id,d.cat_id,d.open,u.user_name FROM " . $ecs->table("drp_shop") . " as d join " . $ecs->table("users") . " as u on d.user_id=u.user_id");
    $smarty->assign('list', $list);
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $smarty->assign('keyword', 'novice');
    $smarty->assign('ur_here', $_LANG['drp_profit']);
    $smarty->display('drp_users.htm');
}

/*------------------------------------------------------ */
//-- 修改店铺状态
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'user_change')
{
    $id = $_GET['id'] ? $_GET['id'] : 0;
    if($id == 0){
        ecs_header("Location: drp.php?act=users\n");
        exit;
    }
    $open = $_GET['open'] > 0 ? 0 : 1;
    $data['open']=$open;
    $db->autoExecute($ecs->table('drp_shop'), $data, 'UPDATE', "id = '$id'");
    ecs_header("Location: drp.php?act=users\n");
    exit;
}

/*------------------------------------------------------ */
//-- 编辑店铺信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'user_edit')
{
    $id = $_GET['id'] ? $_GET['id'] : 0;
    if($id == 0){
        ecs_header("Location: drp.php?act=users\n");
        exit;
    }
    $info = $db->getRow("SELECT d.id,d.shop_name,d.real_name,d.shop_mobile,d.user_id,d.cat_id,d.open,u.user_name FROM " . $ecs->table("drp_shop") . " as d join " . $ecs->table("users") . " as u on d.user_id=u.user_id where d.id = $id");
    $smarty->assign('info', $info);
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $smarty->assign('keyword', 'novice');
    $smarty->assign('ur_here', $_LANG['drp_user_edit']);
    $smarty->display('drp_users_edit.htm');
}
/*------------------------------------------------------ */
//-- 佣金提现管理
/*------------------------------------------------------ */
if($_REQUEST['act'] == 'drp_log'){
	
	assign_query_info();
	if (empty($_REQUEST['is_ajax']))
	{
		$smarty->assign('full_page', 1);
	}
	$smarty->assign('ur_here', $_LANG['drp_log']);
	$list = $db->getAll("SELECT * FROM".$ecs->table('drp_log')."WHERE user_money < 0 ");
	foreach ($list as $v){
		$user_id = $v['user_id'];
		$tm = $v['change_time'];
		$time = date("Y-m-d H:i:s",$tm);
		$money = $v['user_money'];
		$gold = substr($money,1);
	}
	$use = $db->getOne("SELECT user_name FROM".$ecs->table("users")."WHERE user_id =".$user_id);
	$smarty->assign('use',$use);
	$smarty->assign('time',$time);
	$smarty->assign('gold',$gold);
	$smarty->assign('list',$list);
	$smarty->display('drp_log.htm');
	
}
/*------------------------------------------------------ */
//-- 佣金提现管理功能
/*------------------------------------------------------ */
if($_REQUEST['act'] == 'drp_refer'){
	if(IS_GET){
		$id =$_GET['id'];
		$money = $db->getRow("SELECT user_money,user_id FROM".$ecs->table("drp_log")."WHERE log_id =".$id);
		if(!empty($money['user_id'])){
			$shop = $db->getRow("SELECT money,user_id FROM".$ecs->table("drp_shop")."WHERE user_id =".$money['user_id']);
				if($shop['money'] >= abs($money['user_money'])){
					$cash = $shop['money'] + ($money['user_money']);
					$dat['money'] = $cash;
					$up = $db->autoExecute($ecs->table('drp_shop'), $dat, 'UPDATE', "user_id =".$shop['user_id']);
						  if($up == true){
							$user = $db->getRow("SELECT user_money,user_id FROM".$ecs->table("users")."WHERE user_id =".$money['user_id']);
							$total_cash = $user['user_money'] + abs($money['user_money']);
							  if(!$total_cash == 0){
								$dat['user_money'] = $total_cash;
								 $u = $db->autoExecute($ecs->table('users'), $dat, 'UPDATE', "user_id =".$money['user_id']);
								  if($u == true){
								  	sys_msg($_LANG['withdraw_ok'],'',$links[0]['drp_log']);
								  }
							 }  
						} 
				}else{
					sys_msg($_LANG['Lack_of_funds'],'',$links[0]['drp_log']);
				}
		}	
	}
}
/*------------------------------------------------------ */
//-- 订单列表 未分成
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'order_list')
{

    $list = $db->getAll("SELECT * FROM " . $ecs->table("order_info") . " WHERE parent_id > 0  and is_separate = 0");
    foreach($list as $key=>$val){
        $list[$key]['parent_name'] = $db->getOne("SELECT user_name FROM " . $ecs->table("users") . " WHERE user_id = $val[parent_id]");
        $list[$key]['user_name'] = $db->getOne("SELECT user_name FROM " . $ecs->table("users") . " WHERE user_id = $val[user_id]");
    }
    $smarty->assign('list', $list);
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $smarty->assign('keyword', 'novice');
    $smarty->assign('ur_here', $_LANG['drp_profit']);
    $smarty->display('drp_order_list.htm');
}
/*------------------------------------------------------ */
//-- 分成
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'separate')
{
    include_once(BASE_PATH . 'helpers/order_helper.php');
    $oid = (int)$_REQUEST['oid'];

    $row = $db->getRow("SELECT o.order_sn, o.is_separate, (o.goods_amount - o.discount) AS goods_amount, o.user_id FROM " . $GLOBALS['ecs']->table('order_info') . " o".
        " LEFT JOIN " . $GLOBALS['ecs']->table('users') . " u ON o.user_id = u.user_id".
        " WHERE order_id = '$oid'");

    $order_sn = $row['order_sn'];

    if (empty($row['is_separate']))
    {
        // 获取订单中商品


        // 查询商品的所属顶级分类


        // 获取上线 ，

         


        //计算利润


        //将利润保存数据库


        //更改订单分成状态



        if(empty($separate_by))
        {
            //推荐注册分成
            $num = count($affiliate['item']);
            for ($i=0; $i < $num; $i++)
            {
                $affiliate['item'][$i]['level_point'] = (float)$affiliate['item'][$i]['level_point'];
                $affiliate['item'][$i]['level_money'] = (float)$affiliate['item'][$i]['level_money'];
                if ($affiliate['item'][$i]['level_point'])
                {
                    $affiliate['item'][$i]['level_point'] /= 100;
                }
                if ($affiliate['item'][$i]['level_money'])
                {
                    $affiliate['item'][$i]['level_money'] /= 100;
                }
                $setmoney = round($money * $affiliate['item'][$i]['level_money'], 2);
                $setpoint = round($point * $affiliate['item'][$i]['level_point'], 0);
                $row = $db->getRow("SELECT o.parent_id as user_id,u.user_name FROM " . $GLOBALS['ecs']->table('users') . " o" .
                    " LEFT JOIN" . $GLOBALS['ecs']->table('users') . " u ON o.parent_id = u.user_id".
                    " WHERE o.user_id = '$row[user_id]'"
                );
                $up_uid = $row['user_id'];
                if (empty($up_uid) || empty($row['user_name']))
                {
                    break;
                }
                else
                {
                    $info = sprintf($_LANG['separate_info'], $order_sn, $setmoney, $setpoint);
                    log_account_change($up_uid, $setmoney, 0, $setpoint, 0, $info);
                    write_affiliate_log($oid, $up_uid, $row['user_name'], $setmoney, $setpoint, $separate_by);
                }
            }
        }
        else
        {
            //推荐订单分成
            $row = $db->getRow("SELECT o.parent_id, u.user_name FROM " . $GLOBALS['ecs']->table('order_info') . " o" .
                " LEFT JOIN" . $GLOBALS['ecs']->table('users') . " u ON o.pare	nt_id = u.user_id".
                " WHERE o.order_id = '$oid'"
            );
            $up_uid = $row['parent_id'];
            if(!empty($up_uid) && $up_uid > 0)
            {
                $info = sprintf($_LANG['separate_info'], $order_sn, $money, $point);
                log_account_change($up_uid, $money, 0, $point, 0, $info);
                write_affiliate_log($oid, $up_uid, $row['user_name'], $money, $point, $separate_by);
            }
            else
            {
                $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
                sys_msg($_LANG['edit_fail'], 1 ,$links);
            }
        }
        $sql = "UPDATE " . $GLOBALS['ecs']->table('order_info') .
            " SET is_separate = 1" .
            " WHERE order_id = '$oid'";
        $db->query($sql);
    }
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*------------------------------------------------------ */
//-- 修改配置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'updata')
{

    $separate_by = (intval($_POST['separate_by']) == 1) ? 1 : 0;

    $_POST['expire'] = (float) $_POST['expire'];
    $_POST['level_point_all'] = (float)$_POST['level_point_all'];
    $_POST['level_money_all'] = (float)$_POST['level_money_all'];
    $_POST['level_money_all'] > 100 && $_POST['level_money_all'] = 100;
    $_POST['level_point_all'] > 100 && $_POST['level_point_all'] = 100;

    if (!empty($_POST['level_point_all']) && strpos($_POST['level_point_all'],'%') === false)
    {
        $_POST['level_point_all'] .= '%';
    }
    if (!empty($_POST['level_money_all']) && strpos($_POST['level_money_all'],'%') === false)
    {
        $_POST['level_money_all'] .= '%';
    }
    $_POST['level_register_all'] = intval($_POST['level_register_all']);
    $_POST['level_register_up'] = intval($_POST['level_register_up']);
    $temp = array();
    $temp['config'] = array('expire'                => $_POST['expire'],        //COOKIE过期数字
                            'expire_unit'           => $_POST['expire_unit'],   //单位：小时、天、周
                            'separate_by'           => $separate_by,            //分成模式：0、注册 1、订单
                            'level_point_all'       =>$_POST['level_point_all'],    //积分分成比
                            'level_money_all'       =>$_POST['level_money_all'],    //金钱分成比
                            'level_register_all'    =>$_POST['level_register_all'], //推荐注册奖励积分
                            'level_register_up'     =>$_POST['level_register_up']   //推荐注册奖励积分上限
          );
    $temp['item'] = $config['item'];
    $temp['on'] = 1;
    put_affiliate($temp);
    $links[] = array('text' => $_LANG['affiliate'], 'href' => 'affiliate.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*------------------------------------------------------ */
//-- 推荐开关
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'on')
{

    $on = (intval($_POST['on']) == 1) ? 1 : 0;

    $config['on'] = $on;
    put_affiliate($config);
    $links[] = array('text' => $_LANG['affiliate'], 'href' => 'affiliate.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*------------------------------------------------------ */
//-- Ajax修改设置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_point')
{

    /* 取得参数 */
    $key = trim($_POST['id']) - 1;
    $val = (float)trim($_POST['val']);
    $maxpoint = 100;
    foreach ($config['item'] as $k => $v)
    {
        if ($k != $key)
        {
            $maxpoint -= $v['level_point'];
        }
    }
    $val > $maxpoint && $val = $maxpoint;
    if (!empty($val) && strpos($val,'%') === false)
    {
        $val .= '%';
    }
    $config['item'][$key]['level_point'] = $val;
    $config['on'] = 1;
    put_affiliate($config);
    make_json_result(stripcslashes($val));
}
/*------------------------------------------------------ */
//-- Ajax修改设置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_money')
{
    $key = trim($_POST['id']) - 1;
    $val = (float)trim($_POST['val']);
    $maxmoney = 100;
    foreach ($config['item'] as $k => $v)
    {
        if ($k != $key)
        {
            $maxmoney -= $v['level_money'];
        }
    }
    $val > $maxmoney && $val = $maxmoney;
    if (!empty($val) && strpos($val,'%') === false)
    {
        $val .= '%';
    }
    $config['item'][$key]['level_money'] = $val;
    $config['on'] = 1;
    put_affiliate($config);
    make_json_result(stripcslashes($val));
}
/*------------------------------------------------------ */
//-- 删除下线分成
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'del')
{
    $key = trim($_GET['id']) - 1;
    unset($config['item'][$key]);
    $temp = array();
    foreach ($config['item'] as $key => $val)
    {
        $temp[] = $val;
    }
    $config['item'] = $temp;
    $config['on'] = 1;
    $config['config']['separate_by'] = 0;
    put_affiliate($config);
    ecs_header("Location: affiliate.php?act=list\n");
    exit;
}

function get_affiliate()
{
    $config = unserialize($GLOBALS['_CFG']['affiliate']);
    empty($config) && $config = array();

    return $config;
}

function put_affiliate($config)
{
    $temp = serialize($config);
    $sql = "UPDATE " . $GLOBALS['ecs']->table('shop_config') .
           "SET  value = '$temp' " .
           "WHERE code = 'affiliate'";
    $GLOBALS['db']->query($sql);
    clear_all_files();
}
?>