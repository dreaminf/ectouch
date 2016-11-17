<?php

/**
 * 商品控制器
 */
class GoodsController extends PubController{

    private $goods_id;
    public function __construct()
    {
        parent::__construct();

        $this->goods_id = $this->common->get('goods_id');
    }

    /**
     * 商品详情
     */
    public function index(){
        $goods = $this->common->model('Goods')->get_goods_info($this->goods_id);

        $this->common->responseAct($goods);
    }
}