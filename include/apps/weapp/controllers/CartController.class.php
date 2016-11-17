<?php

class CartController extends PubController{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 购物车
     */
    public function index(){
        $cart_goods = $this->common->model('Order')->get_cart_goods();

        $this->common->responseAct($cart_goods);
    }
    /**
     * 添加到购物车
     */
    public function addToCart(){
        $reqInfo = $this->common->get('');
    }

}