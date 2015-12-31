<?php
define('IN_ECTOUCH', true);
define('ROOT_PATH', preg_replace('/plugins\/editor(.*)/i', '', str_replace('\\', '/', __FILE__)));
require(ROOT_PATH . 'admin/includes/init.php');
$root_url = preg_replace('/plugins\/editor(.*)/i', '', PHP_SELF);
if (!empty($_SESSION['admin_id'])){
    if ($_SESSION['action_list'] == 'all'){
        $enable = true;
    } else {
        if (strpos(',' . $_SESSION['action_list'] . ',', ',goods_manage,') === false && strpos(',' . $_SESSION['action_list'] . ',', ',virualcard,') === false && strpos(',' .
        $_SESSION['action_list'] . ',', ',article_manage,') === false)
        {
            $enable = false;
        } else {
            $enable = true;
        }
    }
} else {
    $enable = false;
}
