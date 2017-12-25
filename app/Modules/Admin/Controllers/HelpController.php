<?php

namespace App\Modules\Admin\Controllers;

/**
 * 帮助信息接口
 * Class HelpController
 * @package App\Modules\Admin\Controllers
 */
class HelpController extends BaseController
{
    public function actionIndex()
    {
        $get_keyword = trim($_GET['al']); // 获取关键字
        header("location:http://docs.ectouch.com/do.php?k=" . $get_keyword . "&v=" . $GLOBALS['_CFG']['ecs_version'] . "&l=" . $GLOBALS['_CFG']['lang'] . "&c=" . CHARSET);
    }
}