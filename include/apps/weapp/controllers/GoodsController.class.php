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
        // 更新点击次数
        $data = 'click_count = click_count + 1';
        $this->model->table('goods')->data($data)->where('goods_id = ' . $this->goods_id)->update();

        $this->common->responseAct($goods);
    }
}