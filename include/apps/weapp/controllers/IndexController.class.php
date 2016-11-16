<?php

class IndexController extends PubController{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 主页
     */
    public function index(){

        $array = $this->common->getServer('index')->get_top_banner(256, 6);

        $this->common->responseAct($array);
    }

    /**
     * 品牌
     */
    public function actionVenues(){

        $array = $this->common->getServer('index')->get_part_brand();

        $this->common->responseAct($array);
    }

    /**
     * 商品列表
     */
    public function actionChoice(){

        $array = $this->common->getServer('index')->get_hot_goods_list();

        $this->common->responseAct($array);
    }
}