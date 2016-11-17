<?php

use apps\weapp\services\CommonService;

class PubController extends BaseController{

    protected $authService;
    protected $common;
    protected $appId;
    protected $globalInfo;

    public function __construct()
    {
//        error_reporting(0);
        //加载service文件
        require_once APP_PATH.APP_NAME.'/services/CommonService.php';
        require_once(APP_PATH . 'common/helpers/function.php');

        $this->common = new CommonService();

        $DI['common'] = $this->common;
        $DI['model'] = $this->model;
        $this->authService = $this->common->getServer('auth', $DI);
        $this->globalInfo = $this->authService->authenticate();
    }


}