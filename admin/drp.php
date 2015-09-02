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
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }

    $list = get_user_list();
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $smarty->assign('keyword', 'novice');
    $smarty->assign('ur_here', $_LANG['drp_profit']);
    $smarty->display('drp_users.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $list = get_user_list();
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    make_json_result($smarty->fetch('drp_users.htm'), '',array('filter' => $list['filter'], 'page_count' => $list['page_count']));
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
    // 修改店铺信息
    if($_POST){
        $id = $_POST['id'] ? $_POST['id'] : 0;
        if($id == 0){
            show_message(L('sale_cate_not_empty'));
        }
        $data = $_POST['data'];
        $cat_id = '';
        if($data['cat_id']){
            foreach($data['cat_id'] as $key=>$val){
                $cat_id.=$val.',';
            }
        }else{
            show_message(L('sale_cate_not_empty'));
        }
        $data['cat_id'] = $cat_id;
        $db->autoExecute($ecs->table('drp_shop'), $data, 'UPDATE', "id = '$id'");
        ecs_header("Location: drp.php?act=users\n");
        exit;
    }
    $id = $_GET['id'] ? $_GET['id'] : 0;
    if($id == 0){
        ecs_header("Location: drp.php?act=users\n");
        exit;
    }
    // 获取店铺信息
    $info = $db->getRow("SELECT d.id,d.shop_name,d.real_name,d.shop_mobile,d.user_id,d.cat_id,d.open,u.user_name FROM " . $ecs->table("drp_shop") . " as d join " . $ecs->table("users") . " as u on d.user_id=u.user_id where d.id = $id");
    $smarty->assign('info', $info);

    $catArr = explode(',',$info['cat_id']);
    if($catArr){
        unset($catArr[(count($catArr)-1)]);
    }
    // 获取所有一级分类
    $category = $db->getAll("select cat_id,cat_name from " . $ecs->table("category") . " where parent_id =0");
    if($category){
        foreach($category as $key=>$val){
            if(in_array($val['cat_id'],$catArr)){
                $category[$key]['is_select'] = 1;
            }
        }
    }
    $smarty->assign('category', $category);
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
//-- 查看店铺订单
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'user_order')
{
    // 获取用户id
    $user_id = $_GET['id'] ? $_GET['id'] : 0;
    if($user_id == 0){

        sys_msg($_LANG['empty_id']);
    }
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $smarty->assign('user_id',$user_id);
    $list = get_user_order_list($user_id);
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $smarty->assign('ur_here', $_LANG['drp_user_edit']);
    $smarty->display('drp_user_order.htm');
}

/*------------------------------------------------------ */
//-- 查看店铺订单
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'user_log')
{
    // 获取用户id
    $user_id = $_GET['id'] ? $_GET['id'] : 0;
    if($user_id == 0){

        sys_msg($_LANG['empty_id']);
    }
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $smarty->assign('user_id',$user_id);
    $list = get_user_log_list($user_id);
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $smarty->assign('ur_here', $_LANG['drp_user_edit']);
    $smarty->display('drp_user_log.htm');
}

/*------------------------------------------------------ */
//-- 佣金管理
/*------------------------------------------------------ */
if($_REQUEST['act'] == 'drp_log'){
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    //分页
    $count = $db->getOne("SELECT COUNT(*) FROM".$ecs->table('drp_log'));
    //页面
    $smarty->assign('ur_here', $_LANG['drp_log']);
    $list = $db->getAll("SELECT * FROM".$ecs->table('drp_log')."WHERE user_money < 0 ");
    foreach ($list as $key=>$val){
        $list[$key]['change_time'] = date("Y-m-d H:i:s",$val['change_time']);
        $list[$key]['user_money'] = substr($val['user_money'],1);
        $list[$key]['user_name'] = $db->getOne("SELECT user_name FROM".$ecs->table("users")."WHERE user_id =".$val['user_id']);
        $list[$key]['shop_name'] = $db->getOne("SELECT shop_name FROM".$ecs->table('drp_shop')."WHERE user_id =".$val['user_id']);
        if($val['change_type'] == 0){
            $list[$key]['status'] = '未支付';
        }
        if($val['change_type'] == 1){
            $list[$key]['status'] = '已经支付';
        }
    }
    $smarty->assign('count',$count);
    $smarty->assign('list',$list);
    $smarty->display('drp_log.htm');
}
/*------------------------------------------------------ */
//-- 佣金提现管理功能
/*------------------------------------------------------ */
if($_REQUEST['act'] == 'drp_refer'){
    if(IS_GET){
        $id =$_GET['id'];
        $money = $db->getRow("SELECT user_money,user_id,change_type FROM".$ecs->table("drp_log")."WHERE log_id =".$id);
        if(intval($money['change_type']) === 0){
            $shop = $db->getRow("SELECT money,user_id FROM".$ecs->table("drp_shop")."WHERE user_id =".$money['user_id']);
            if($shop['money'] >= abs($money['user_money'])){
                $cash = $shop['money'] + ($money['user_money']);
                $dat['money'] = $cash;
                $age['change_type'] = 1;
                $db->autoExecute($ecs->table('drp_log'), $age, 'UPDATE', "user_id =".$money['user_id']);
                $up = $db->autoExecute($ecs->table('drp_shop'), $dat, 'UPDATE', "user_id =".$shop['user_id']);
                if($up == true){
                    $user = $db->getRow("SELECT user_money,user_id FROM".$ecs->table("users")."WHERE user_id =".$money['user_id']);
                    if(!empty($user)){
                        $total_cash = $user['user_money'] + abs($money['user_money']);
                        if(!$total_cash == 0){
                            $dat['user_money'] = $total_cash;
                            $u = $db->autoExecute($ecs->table('users'), $dat, 'UPDATE', "user_id =".$money['user_id']);
                            if($u == true){
                                $links[0]['href'] = 'drp.php?act=drp_log';
                                sys_msg($_LANG['withdraw_ok'],'',$links);
                            }
                        }
                    }
                }
            }else{
                $links[0]['href'] = 'drp.php?act=drp_log';
                sys_msg($_LANG['Lack_of_funds'],'',$links);
            }
        }else{
            $links[0]['href'] = 'drp.php?act=drp_log';
            sys_msg($_LANG['The_extracted'],'',$links);
        }
    }
}
/*------------------------------------------------------ */
//-- 佣金提现删除
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'order_delete'){
    if(IS_GET){
        $id=$_GET['id'];
        $money = $db->getRow("SELECT change_type,user_id FROM ".$ecs->table("drp_log")." WHERE log_id =".$id);
        if(intval($money['change_type']) === 1){
            $sql = "DELETE FROM " . $ecs->table('drp_log') .
                " WHERE user_id = ".$money['user_id'];
            $delete = $db->query($sql);
            if($delete == true){
                $links[0]['href'] = 'drp.php?act=drp_log';
                sys_msg($_LANG['delete_Success'],'',$links);

            }
        }
    }
}
/*------------------------------------------------------ */
//-- 订单列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'order_list')
{
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $is_separate = $_GET['is_separate'] ? $_GET['is_separate'] : 0;
    $smarty->assign('is_separate', $is_separate);
    $list = get_order_list($is_separate);
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

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

    $row = $db->getRow("SELECT o.order_id,o.order_sn, o.is_separate, (o.goods_amount - o.discount) AS goods_amount, o.user_id FROM " . $GLOBALS['ecs']->table('order_info') . " o".
        " LEFT JOIN " . $GLOBALS['ecs']->table('users') . " u ON o.user_id = u.user_id".
        " WHERE order_id = '$oid'");

    $order_sn = $row['order_sn'];

    if (empty($row['is_separate']))
    {
        // 获取订单中商品
        $parent_id = $db->getOne("SELECT parent_id FROM " . $GLOBALS['ecs']->table('order_info') .  " where order_id = $oid");
        $goods_list = $db->getAll("SELECT goods_id,goods_price,goods_number FROM " . $GLOBALS['ecs']->table('order_goods') .  " where order_id = $oid");

        $data1 = $data2 = $data3 = array(
            'user_id'=>0,
            'profit'=>0,
        );

        foreach($goods_list as $key=>$val){
            $profit = get_drp_profit($val['goods_id']);
            if(!$profit){
                $profit['profit1'] = 0;
                $profit['profit2'] = 0;
                $profit['profit3'] = 0;
            }

            // 一级分销商利润
            $data1['user_id'] = $parent_id;
            $data1['profit']+= $val['goods_price']*$profit['profit1']/100*$val['goods_number'];
            // 二级分销商
            $data2['user_id'] = $db->getOne("SELECT parent_id FROM " . $GLOBALS['ecs']->table('users') .  " where user_id = $data1[user_id]");
            if($data2['user_id']){
                $data2['profit']+= $val['goods_price']*$profit['profit2']/100*$val['goods_number'];
                // 三级分销商
                $data3['user_id'] = $db->getOne("SELECT parent_id FROM " . $GLOBALS['ecs']->table('users') .  " where user_id = $data2[user_id]");
                if($data3['user_id']){
                    $data3['profit']+= $val['goods_price']*$profit['profit3']/100*$val['goods_number'];
                }
            }
        }

        if($data1['profit'] > 0){
            $info = sprintf($_LANG['separate_info'], $row['order_sn'], $data1['profit'], $data1['profit']);;
            drp_log_change($data1['user_id'], $data1['profit'], $data1['profit'], $info);
        }
        if($data2['profit'] > 0){
            $info = sprintf($_LANG['separate_info'], $row['order_sn'], $data2['profit'], $data2['profit']);;
            drp_log_change($data2['user_id'], $data2['profit'], $data2['profit'], $info);
        }
        if($data3['profit'] > 0){
            $info = sprintf($_LANG['separate_info'], $row['order_sn'], $data3['profit'], $data3['profit']);;
            drp_log_change($data3['user_id'], $data3['profit'], $data3['profit'], $info);
        }
        $sql = "UPDATE " . $GLOBALS['ecs']->table('order_info') .
            " SET is_separate = 1" .
            " WHERE order_id = $oid LIMIT 1";
        $db->query($sql);

    }
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'drp.php?act=order_list');
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


/**
 * 取得分销商列表
 * @param   int     $user_id    用户id
 * @param   string  $account_type   帐户类型：空表示所有帐户，user_money表示可用资金，
 *                  frozen_money表示冻结资金，rank_points表示等级积分，pay_points表示消费积分
 * @return  array
 */
function get_user_list()
{
    /* 初始化分页参数 */
    $filter = array(

    );

    /* 查询记录总数，计算分页数 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('drp_shop');
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);

    /* 查询记录 */
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('drp_shop') .
        " ORDER BY id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['create_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['create_time']);
        $row['user_name'] = $GLOBALS['db']->getOne("select user_name from ".$GLOBALS['ecs']->table('users') ." where user_id = ".$row['user_id']);
        $arr[] = $row;
    }
    return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}


/**
 * 取得分销商订单
 * @param   int     $user_id    用户id
 * @param   string  $account_type   帐户类型：空表示所有帐户，user_money表示可用资金，
 *                  frozen_money表示冻结资金，rank_points表示等级积分，pay_points表示消费积分
 * @return  array
 */
function get_user_order_list($user_id)
{
    /* 初始化分页参数 */
    $filter = array(
        'id'=>$user_id,
    );
    /* 查询记录总数，计算分页数 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info'). " WHERE parent_id = $user_id  and is_separate = 0";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);

    /* 查询记录 */
    $sql = "SELECT * FROM " .  $GLOBALS['ecs']->table('order_info'). " WHERE parent_id = $user_id  and is_separate = 0 " .
        " ORDER BY order_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
        $row['user_name'] = $GLOBALS['db']->getOne("select user_name from ".$GLOBALS['ecs']->table('users') ." where user_id = ".$row['user_id']);
        $row['parent_name'] = $GLOBALS['db']->getOne("select user_name from ".$GLOBALS['ecs']->table('users') ." where user_id = ".$row['parent_id']);
        $arr[] = $row;
    }
    return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}


/**
 * 取得分销商佣金
 * @param   int     $user_id    用户id
 * @param   string  $account_type   帐户类型：空表示所有帐户，user_money表示可用资金，
 *                  frozen_money表示冻结资金，rank_points表示等级积分，pay_points表示消费积分
 * @return  array
 */
function get_user_log_list($user_id)
{
    /* 初始化分页参数 */
    $filter = array(
        'id'=>$user_id,
    );
    /* 查询记录总数，计算分页数 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('drp_log'). " WHERE user_id = $user_id  and user_money > 0";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);

    /* 查询记录 */
    $sql = "SELECT * FROM " .  $GLOBALS['ecs']->table('drp_log'). " WHERE user_id = $user_id  and user_money > 0 " .
        " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['change_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['change_time']);
        $arr[] = $row;
    }
    return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}


/**
 * 取得订单
 * @param   int     $user_id    用户id
 * @param   string  $account_type   帐户类型：空表示所有帐户，user_money表示可用资金，
 *                  frozen_money表示冻结资金，rank_points表示等级积分，pay_points表示消费积分
 * @return  array
 */
function get_order_list($is_separate)
{
    /* 初始化分页参数 */
    $filter = array(
        'is_separate'=>$is_separate,
    );
    /* 查询记录总数，计算分页数 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info'). " WHERE parent_id > 0  and is_separate = $is_separate";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);

    /* 查询记录 */
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_info'). " WHERE parent_id > 0  and is_separate = $is_separate" .
        " ORDER BY order_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
        $row['user_name'] = $GLOBALS['db']->getOne("select user_name from ".$GLOBALS['ecs']->table('users') ." where user_id = ".$row['user_id']);
        $row['parent_name'] = $GLOBALS['db']->getOne("select user_name from ".$GLOBALS['ecs']->table('users') ." where user_id = ".$row['parent_id']);
        $arr[] = $row;
    }
    return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

/**
 * 获取佣金比例
 * @param $goods_id
 */
function get_drp_profit($goods_id=0){
    if($goods_id == 0 ){
        return false;
    }
    $id = $GLOBALS['db']->getOne("select cat_id from ".$GLOBALS['ecs']->table('goods') ." where goods_id = ".$goods_id);
    $id = get_goods_cat($id);
    $profit = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('drp_profit') ." where cate_id = ".$id);
    return $profit ? $profit : false;
}

function get_goods_cat($id){
    $parent_id = $GLOBALS['db']->getOne("select parent_id from ".$GLOBALS['ecs']->table('category') ." where cat_id = ".$id);
    if($parent_id==0){
        return $id;
    }else{
        $id = get_goods_cat($parent_id);
        return $id;
    }
}

/**
 * 记录帐户变动
 * @param   int     $user_id        用户id
 * @param   float   $user_money     可用余额变动
 * @param   int     $pay_points     消费积分变动
 * @param   string  $change_desc    变动说明
 * @return  void
 */
function drp_log_change($user_id, $user_money = 0, $pay_points = 0, $change_desc = '')
{
    /* 插入帐户变动记录 */
    $drp_log = array(
        'user_id'       => $user_id,
        'user_money'    => $user_money,
        'pay_points'    => $pay_points,
        'change_time'   => gmtime(),
        'change_desc'   => $change_desc,
        'change_type'   => 0
    );
    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('drp_log'), $drp_log, 'INSERT');

    /* 更新用户信息 */
    $sql = "UPDATE " . $GLOBALS['ecs']->table('drp_shop') .
        " SET money = money + ('$user_money')" .
        " WHERE user_id = '$user_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
}
?>