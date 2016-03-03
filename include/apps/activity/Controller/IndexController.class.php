<?php
namespace activity\Controller;

use base\Controller\FrontendController;

class IndexController extends FrontendController
{

    public function __construct(){
    	parent::__construct();
    	require_once(ROOT_PATH . 'include/helpers/lib_order.php');
		include_once(ROOT_PATH . 'include/helpers/lib_transaction.php');
		require_once(ROOT_PATH . 'include/languages/' .C('lang'). '/shopping_flow.php');
		require_once(ROOT_PATH . 'include/languages/' .C('lang'). '/user.php');
    }

    /**
     * 优惠活动
     */
    public function indexAction() {
		$position = assign_ur_here(0, $_LANG['shopping_activity']);
		$this->assign('page_title', $position['title']);
		$this->assign('lang', $_LANG);
		$this->display('activity.dwt');
    }

    /**
     * 活动商品
     */
    public function activityAction(){
        $position = assign_ur_here(0, $_LANG['shopping_activity']);
        $this->assign('page_title', $position['title']);
        $this->assign('lang', $_LANG);
        $this->display('activity_detail.dwt');
    }

}