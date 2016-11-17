<?php

/**
 * Class ClassifyController
 * 分类控制器
 */

class ClassifyController extends PubController{

    public function __construct()
    {
        parent::__construct();
        require_once(APP_PATH . 'common/helpers/function.php');
    }

    /**
     * 分类
     */
    public function index(){
        $category = model('CategoryBase')->get_categories_tree();
        dump($category);
        exit;

        $this->common->responseAct($category);
    }
}