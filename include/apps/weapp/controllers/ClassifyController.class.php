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
     * 分类列表
     */
    public function index(){
        $category = $this->common->model('CategoryBase')->get_categories_tree();

        $this->common->responseAct($category);
    }

    /**
     * 分类及商品
     */
    public function category(){
        $cat_id = $this->common->get('cat_id');
        $cat =$this->common->getServer('classify')->get_cat_info($cat_id);  // 获得分类的相关信息
        $goods_list =$this->common->getServer('classify')->category_get_goods();  // 获得分类的商品

        $this->common->responseAct($cat);
    }
}