<?php

namespace apps\weapp\controllers;

class BrandController extends BaseController{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 品牌列表页面
     */
    public function actionIndex(){
        $array = array();

        $this->responseAct($array);
    }
}