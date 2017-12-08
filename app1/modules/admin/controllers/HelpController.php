<?php

namespace app\modules\admin\controllers;

/**
 * 帮助信息接口
 * Class HelpController
 * @package app\modules\admin\controllers
 */
class HelpController extends Controller
{
    public function actionIndex()
    {
        $get_keyword = trim($_GET['al']); // 获取关键字
        header("location:http://docs.ectouch.com/do.php?k=" . $get_keyword . "&v=" . $GLOBALS['_CFG']['ecs_version'] . "&l=" . $GLOBALS['_CFG']['lang'] . "&c=" . CHARSET);
    }
}