<?php

namespace apps\weapp\controllers;

class AuthController extends BaseController{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取授权
     * @param $appId
     * @param $app_secret
     * @return $access_token
     */
    public function actionAuthorize(){
        $array = array('code'=>0, 'msg'=>'');

        $data['widget_id'] = $this->common->get('app_id');
        $data['union_id'] = $this->common->get('union_id');
        $data['is_agree'] = 0;
        $data['status'] = 1;

        $tokenArray = $this->authService->generateAccessToken();
        $data['access_token'] = $tokenArray['token'];
        $data['access_token_expire'] = $tokenArray['expire'];

        $authInfoId = $this->authService->generateAuthInfo($data);

        $array['token'] = $tokenArray['token'];
        $this->common->responseAct($array);
    }


}