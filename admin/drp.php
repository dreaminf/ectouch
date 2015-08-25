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
//-- 申请分销温馨提示
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'apply')
{
    if($_POST){
        $data = $_POST['data'];
        $db->autoExecute($ecs->table('drp_config'), $data, 'UPDATE', "keyword = 'apply'");
        ecs_header("Location: drp.php?act=apply\n");
        exit;
    }
    $info = $db->getRow("SELECT * FROM " . $ecs->table("drp_config") . " WHERE keyword ='apply'");
    $smarty->assign('info', $info);
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $smarty->assign('keyword', 'apply');
    $smarty->assign('ur_here', $_LANG['drp_profit']);
    $smarty->display('drp_apply.htm');
}

/*------------------------------------------------------ */
//-- 新手必读
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'novice')
{
    if($_POST){
        $data = $_POST['data'];
        $db->autoExecute($ecs->table('drp_config'), $data, 'UPDATE', "keyword = 'novice'");
        ecs_header("Location: drp.php?act=novice\n");
        exit;
    }
    $info = $db->getRow("SELECT * FROM " . $ecs->table("drp_config") . " WHERE keyword ='novice'");
    $smarty->assign('info', $info);
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
    $smarty->assign('keyword', 'novice');
    $smarty->assign('ur_here', $_LANG['drp_profit']);
    $smarty->display('drp_novice.htm');
}
/*------------------------------------------------------ */
//-- 增加下线分配方案
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    if (count($config['item']) < 5)
    {
        //下线不能超过5层
        $_POST['level_point'] = (float)$_POST['level_point'];
        $_POST['level_money'] = (float)$_POST['level_money'];
        $maxpoint = $maxmoney = 100;
        foreach ($config['item'] as $key => $val)
        {
            $maxpoint -= $val['level_point'];
            $maxmoney -= $val['level_money'];
        }
        $_POST['level_point'] > $maxpoint && $_POST['level_point'] = $maxpoint;
        $_POST['level_money'] > $maxmoney && $_POST['level_money'] = $maxmoney;
        if (!empty($_POST['level_point']) && strpos($_POST['level_point'],'%') === false)
        {
            $_POST['level_point'] .= '%';
        }
        if (!empty($_POST['level_money']) && strpos($_POST['level_money'],'%') === false)
        {
            $_POST['level_money'] .= '%';
        }
        $items = array('level_point'=>$_POST['level_point'],'level_money'=>$_POST['level_money']);
        $links[] = array('text' => $_LANG['affiliate'], 'href' => 'affiliate.php?act=list');
        $config['item'][] = $items;
        $config['on'] = 1;
        $config['config']['separate_by'] = 0;

        put_affiliate($config);
    }
    else
    {
       make_json_error($_LANG['level_error']);
    }

    ecs_header("Location: affiliate.php?act=query\n");
    exit;
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